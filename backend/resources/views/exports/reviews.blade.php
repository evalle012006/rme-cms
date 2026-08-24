<table>
    <thead>
    <tr>
        <th>Project Name</th>
        <th>User</th>
        <th>Title</th>
        <th>Year Of Completion</th>
        <th>Buyer Type</th>
        @foreach($categories as $category)
            <th>{{$category}}</th>
        @endforeach
        <th>Overall ratings</th>
        <th>Recommended</th>
        <th>Review Tags</th>
        <th>Comment</th>
    </tr>
    </thead>
    <tbody>
    @foreach($reviews as $review)
        <tr>
            <td>{{ $property_name }}</td>
            <td>{{ $review->users->display_name }}</td>
            <td>{{ $review->title }}</td>
            <td>{{ $review->year_of_completion }}</td>
            <td>{{ $review->buyerTypeRow->name }}</td>
            @foreach($categories as $title)
                <td>{{$review->rating_titles[$title]}}</td>
            @endforeach
            <td>{{ $review->avg_rating }}</td>
            <td>{{ $review->recommendation ? 'Yes' : 'No' }}</td>
            <td>{{ $review->tags }}</td>
            <td>{{ $review->comment }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
