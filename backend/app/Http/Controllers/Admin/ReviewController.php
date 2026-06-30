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
use GuzzleHttp\Exception\ServerException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException; 

class ReviewController extends BaseController
{

    public function getDatatableReview(Request $request)
    {
        $reviews = Review::with('property')
            ->select('reviews.*')
            ->where('status', $request->status);

        return datatables($reviews)->toJson();
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
            'reviewTags'
        ])->find($id);
        $review->suburb_list = Suburbs::selectRaw('name as text')
            ->where('postcode', $review->post_code)
            ->get();
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
            \Mail::to($reviewData['users']['email'])->send(new \App\Mail\ApprovedReviewMail($reviewData));
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

    public function deleteReview($id)
    {
        $review = Review::findOrFail($id);
        $review->delete();
        return $this->formatSuccessResponse('Review Deleted', null);
    }

}
