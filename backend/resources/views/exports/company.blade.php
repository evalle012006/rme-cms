<table>
    <thead>
    <tr>
        <th>Company Name</th>
        <th>Project Name</th>
        <th>State</th>
        <th>City</th>
        <th>Post code</th>
        <th>Suburb</th>
        <th>Review Title</th>
        <th>Category</th>
        <th>Display Name</th>
        <th>Buyer Type</th>
        <th>Year Of Completion</th>
        @foreach($categories as $category)
            <th>{{$category}}</th>
        @endforeach
        <th>Overall Rating</th>
        <th>Rating Tags</th>
        <th>Comment</th>
    </tr>
    </thead>
    <tbody>
    @foreach($reviews as $review)
        <tr>
            <td>{{ $review->property->company ? $review->property->company->company_name : '-' }}</td>
            <td>{{ $review->property->project_name }}</td>
            <td>{{ $review->property->state }}</td>
            <td>{{ $review->property->city }}</td>
            <td>{{ $review->property->post_code }}</td>
            <td>{{ $review->property->suburb }}</td>
            <td>{{ $review->title }}</td>
            <td>{{ $review->category->name }}</td>
            <td>{{ $review->users->display_name }}</td>
            <td>{{ $review->buyerTypeRow->value_text }}</td>
            <td>{{ $review->year_of_completion }}</td>
            @foreach($categories as $category)
                <th>{{$review->rating_by_title[$category]}}</th>
            @endforeach
            <td>{{ $review->overall_rating }}</td>
            <td>{{ $review->tags }}</td>
            <td>{{ $review->comment }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
