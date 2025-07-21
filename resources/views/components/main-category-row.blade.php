<div>
    @foreach($categories as $category)
        <div class="category">
            <h2>{{ $category->name ?? 'FIXME' }}</h2>
            <!-- Add more content to display category details here -->
        </div>
    @endforeach
</div>
