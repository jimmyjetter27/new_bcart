<div>
    <h2>Title: {{ $photo->title }}</h2>
    <p><strong>Description:</strong> {{ $photo->description }}</p>
    <p><strong>Price:</strong> {{ $photo->price ?? 'N/A' }}</p>
    <p><strong>Uploaded by:</strong> {{ $creative->name }}</p>
    <p><strong>Categories:</strong> {{ $categories->pluck('photo_category')->join(', ') }}</p>
    <p><strong>Created on:</strong> {{ $created_at }}</p>
</div>
