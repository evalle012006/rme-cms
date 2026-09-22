<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Models\Properties;
use App\Models\PropertyCategory;
use App\Models\RatingConfiguration;
use App\Models\Review;
use App\Models\ReviewStates;
use App\Models\ReviewTags;
use App\Models\SelectMaster;
use App\Models\Suburbs;
use App\Models\Tags;
use App\Services\ResendService;
use App\Support\ReviewLocalityExceptionReport;
use App\Support\StateResolver;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException; 

class ReviewController extends BaseController
{

    public function getDatatableReview(Request $request)
    {
        $reviews = Review::with(['property.company:id,company_name', 'locality'])
            ->select('reviews.*')
            ->where('status', $request->status);

        $this->applyLocalityFilters($reviews, $request);

        return datatables($reviews)->toJson();
    }

    /**
     * Reviews that could not be resolved to a single locality.
     *
     * Returns a download URL rather than the rows so the portal can hand the
     * client a CSV to work through. Regenerated on request, so it always
     * reflects the current state of the data.
     */
    public function getLocalityExceptionReport()
    {
        try {
            $rows = ReviewLocalityExceptionReport::rows();

            return $this->formatSuccessResponse('success', [
                'count' => count($rows),
                'url' => empty($rows) ? null : ReviewLocalityExceptionReport::store($rows),
            ]);
        } catch (\Exception $e) {
            Log::error('REVIEW_LOCALITY_REPORT_ERROR: ' . $e->getMessage());
            return $this->formatFailureResponse('Error generating the locality exception report', 500);
        }
    }

    /**
     * Re-derive the stored state, suburb and postcode from the referenced
     * locality, so the legacy text columns always agree with the FK.
     *
     * @throws ValidationException when the locality does not resolve
     */
    private function applyLocalityToReviewData(array $data)
    {
        $locality = empty($data['locality_id'])
            ? null
            : Suburbs::find($data['locality_id']);

        if (!$locality) {
            throw ValidationException::withMessages([
                'locality_id' => 'Select a State, Suburb and Postcode from the list',
            ]);
        }

        $stateId = StateResolver::id($locality->state_code);

        $data['locality_id'] = (int) $locality->id;
        $data['suburb'] = $locality->name;
        $data['post_code'] = (string) $locality->postcode;
        if ($stateId) {
            $data['state'] = $stateId;
        }

        return $data;
    }

    /**
     * Location filters run through the locality_id foreign key.
     *
     * Filtering used to compare the free-text suburb column, which could not
     * match reliably. State and postcode are constrained through the related
     * locality row so every combination narrows the same set.
     */
    private function applyLocalityFilters($query, Request $request)
    {
        if ($request->filled('locality_id')) {
            $query->where('reviews.locality_id', $request->input('locality_id'));
        }

        $stateCode = $request->input('state_code');
        $postcode = $request->input('postcode');

        if (!$request->filled('state_code') && !$request->filled('postcode')) {
            return $query;
        }

        return $query->whereHas('locality', function ($localityQuery) use ($stateCode, $postcode) {
            if (!empty($stateCode)) {
                $localityQuery->where('state_code', strtoupper(trim($stateCode)));
            }
            if (!empty($postcode)) {
                $localityQuery->where('postcode', trim($postcode));
            }
        });
    }

    public function getReviewById($id)
    {
        $review = Review::with([
            'property' => function ($qry) {
                $qry->with('company:id,company_name,is_from');
            },
            'users' => function ($qry) {
                $qry->select('id', 'display_name', 'firstname', 'lastname', 'email', 'email_verified_at');
            },
            'reviewTags',
            'locality'
        ])->find($id);
        $review->suburb_list = Suburbs::selectRaw('name as text')
            ->where('postcode', $review->post_code)
            ->get();
        $review->state = $this->resolveReviewStateId($review);
        $review->tags_list = Tags::where('is_active', true)->get();
        if ($review->property->developer_name) {
            $review->property_list = Properties::select('id', 'project_name')
                ->where('developer_name', $review->property->developer_name)->get();
        } else {
            $review->property_list = Properties::select('id', 'project_name')->where('id', $review->property_id)->get();
        }
        return $this->formatSuccessResponse('success', $review);
    }

    public function updateReview($id, Request $request)
    {
        try {
            $data = $this->validate($request, Review::$rules_admin);
            $reviewData = Review::find($id);

            if (!$reviewData) {
                return $this->formatFailureResponse('Review not found', 404);
            }

            $data = $this->applyLocalityToReviewData($data);

            $oldStatus = $reviewData->status;

            // Handle photo upload
            if ($request->hasFile('review_image')) {
                $data['review_image'] = $this->saveToS3(
                    $request->file('review_image'),
                    self::REVIEW_STORAGE_PATH
                );
            } else {
                unset($data['review_image']); // preserve existing
            }

            // Handle doc upload
            if ($request->hasFile('review_docs')) {
                $data['review_docs'] = $this->saveToS3(
                    $request->file('review_docs'),
                    self::REVIEWDOCS_STORAGE_PATH
                );
            } else {
                unset($data['review_docs']); // preserve existing
            }

            $reviewData->update($data);

            // Tags — frontend sends array of integer IDs
            $commentTags = $request->review_tags ?? [];
            $tags = [];
            foreach ($commentTags as $tagId) {
                $tags[] = ['review_id' => $id, 'tag_id' => (int)$tagId];
            }
            ReviewTags::where('review_id', $id)->delete();
            if (!empty($tags)) {
                ReviewTags::insert($tags);
            }

            if ((int)$data['status'] === 1 && $oldStatus === 0) {
                $property = Properties::find($data['property_id']);
                if ($property && $property->status == 0 && $property->is_from == 'frontend') {
                    $property->update(['status' => 1]);
                }
                if ($property && $property->company && $property->company->status === 0) {
                    $property->company->update(['status' => 1]);
                }
                try {
                    $this->sendEmail($id);
                } catch (\Exception $e) {
                    Log::error('REVIEW_APPROVE_MAIL_ERROR: ' . $e->getMessage());
                }
            }

            return $this->formatSuccessResponse('Review Updated', $reviewData->fresh());
        } catch (ValidationException $e) {
            return $this->apiFormatFailureResponse('false', $e->errors());
        } catch (\Exception $e) {
            Log::error('REVIEW_UPDATE_ERROR: ' . $e->getMessage());
            return $this->formatFailureResponse('Error updating review', 500);
        }
    }

    public function getBuyerTypes()
    {
        $data = [];
        $data['buyertype'] = SelectMaster::with('optionSelect')->where('id', 1)->first()->toArray();
        $data['categories'] = PropertyCategory::get()->toArray();
        return $this->formatSuccessResponse('success', $data);
    }

    public function getReviewStates()
    {
        $states = ReviewStates::select('id', 'name as state_name')->get()->toArray();
        return $this->formatSuccessResponse('success', $states);
    }

    public function changeReviewStatus(Request $request)
    {
        $reviewId = $request->id;
        $newStatus = $request->status;

        $validator = Validator::make(['id' => $reviewId], ['id' => 'required|exists:reviews,id']);
        if ($validator->fails()) {
            return $this->formatFailureResponse('Review does not exists', 404);
        }
        try {
            $reviewData = Review::find($reviewId);
            $oldStatus = $reviewData->status;

            $updated = Review::where('id', $reviewId)->update(['status' => $newStatus]);
            if ($updated) {
                if ((int)$newStatus === 1 && $oldStatus === 0) {

                    if ($reviewData->property->status == 0 && $reviewData->property->is_from == 'frontend') {
                        $update['status'] = 1;
                        $reviewData->property->update($update);
                    }

                    if ($reviewData->property->company && $reviewData->property->company->status === 0) {
                        $reviewData->property->company->update(['status' => 1]);
                    }
                    // send mail on review approve
                    $this->sendEmail($reviewId);
                    // end
                }
                return $this->formatSuccessResponse('success', []);
            }
            return $this->formatFailureResponse('Server error', 500);
        } catch (\Exception $e) {
            return $this->formatFailureResponse('Server error', 500);
        }
    }

    public function sendEmail($id)
    {
        try {
            $reviewData = Review::select('id', 'user_id', 'property_id', 'title')
                ->whereId($id)
                ->with('property:id,project_name', 'users:id,email')
                ->first()
                ->toArray();
            app(ResendService::class)->send(
                $reviewData['users']['email'],
                'Your review is approved and live!',
                'email-template.approved-review',
                ['details' => $reviewData]
            );
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getSuburbs(Request $request)
    {
        $postCode = $request->post_code;

        if (!empty($postCode)) {
            $data = Suburbs::selectRaw('name as text')
                ->where('postcode', $postCode)
                ->get();

            return $this->formatSuccessResponse('success', $data);
        }
        return $this->formatSuccessResponse('success', []);
    }

    public function getSuburbsByState(Request $request)
    {
        $stateId = $request->state_id;

        if (empty($stateId)) {
            return $this->formatSuccessResponse('success', []);
        }

        $state = ReviewStates::select('id', 'name')->find($stateId);
        if (!$state) {
            return $this->formatSuccessResponse('success', []);
        }

        $data = Suburbs::where('state', $state->name)
            ->orderBy('name')
            ->distinct()
            ->pluck('name')
            ->map(function ($name) {
                return [
                    'id' => $name,
                    'name' => $name,
                ];
            })
            ->values();

        return $this->formatSuccessResponse('success', $data);
    }

    public function getSuburbsByStateWithPostcode(Request $request)
    {
        $stateId = $request->state_id;

        if (empty($stateId)) {
            return $this->formatSuccessResponse('success', []);
        }

        $state = ReviewStates::select('id', 'name')->find($stateId);
        if (!$state) {
            return $this->formatSuccessResponse('success', []);
        }

        $data = Suburbs::where('state', $state->name)
            ->select('name', 'postcode')
            ->distinct()
            ->orderBy('name')
            ->orderBy('postcode')
            ->get()
            ->map(function ($row) {
                return [
                    'id' => $row->name,
                    'name' => $row->name,
                    'postcode' => (string) $row->postcode,
                ];
            })
            ->values();

        return $this->formatSuccessResponse('success', $data);
    }

    /**
     * Reviews.store state as review_states.id. Older/seeded rows may have 0
     * or a state name coerced to 0. Resolve a valid id from suburb + postcode
     * so the edit form can prefills.
     */
    private function resolveReviewStateId($review)
    {
        $stateId = (int) $review->state;
        if ($stateId && ReviewStates::where('id', $stateId)->exists()) {
            return $stateId;
        }

        if ($review->suburb || $review->post_code) {
            $suburbQuery = Suburbs::query();
            if ($review->suburb) {
                $suburbQuery->where('name', $review->suburb);
            }
            if ($review->post_code) {
                $suburbQuery->where('postcode', $review->post_code);
            }
            $suburbRow = $suburbQuery->first();
            if ($suburbRow) {
                $matched = ReviewStates::where('name', $suburbRow->state)->first();
                if ($matched) {
                    return $matched->id;
                }
            }
        }

        return $review->state;
    }

    public function deleteReview($id)
    {
        $review = Review::findOrFail($id);
        $review->delete();
        return $this->formatSuccessResponse('Review Deleted', null);
    }

}
