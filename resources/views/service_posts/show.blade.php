@extends('dashboard')
@section('title', "show Posts")
@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                        <div class="row justify-content-center">
                                <div class="col-md-8 mb-3">
                                    <div class="card ">
                                        <div class="card-header
                                        @if($servicePost->have_badge == 'عادي')
                                            bg-primary
                                        @elseif($servicePost->have_badge == 'ذهبي')
                                           bg-warning
                                        @elseif($servicePost->have_badge == 'ماسي')
                                           bg-info
                                        @endif
                                        ">
                                            <div class="row justify-content-between">
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        @if ($servicePost->photos->count() > 0)
                                                            @php
                                                                $firstPhoto = $servicePost->user->photos->first();
                                                            @endphp

                                                            @if ($firstPhoto)
                                                                <img src="{{ asset('storage/' . $firstPhoto->src) }}" class="img-circle" alt="Profile Picture" width="50">
                                                            @else
                                                                <!-- Handle the case where the first photo is null -->
                                                                <img src="{{ asset('storage/photos/avatar1.png') }}" class="img-circle" alt="Profile Picture" width="50">
                                                            @endif
                                                        @else
                                                            <!-- Handle the case where there are no photos -->
                                                            <img src="{{ asset('storage/photos/avatar1.png') }}" class="img-circle" alt="Profile Picture" width="50">
                                                        @endif
                                                    </div>

                                                    <div>
                                                        <h4>{{ $servicePost->user->user_name }}</h4>
                                                    </div>

                                                </div>
                                                <div class="d-flex align-items-center">
                                                    <h5>{{ $servicePost->title }}</h5>


                                                </div>
                                            </div>


                                        </div>
                                        <div class="card-body text-right">
                                            <p>{{ $servicePost->description }}</p>
                                            <div>
                                                @if ($servicePost->photos->count() > 0)
                                                    <div class="row justify-content-center">
                                                        @foreach ($servicePost->photos as $photo)
                                                            <div class="col-md-12   mb-3">
                                                                @php
                                                                    $extension = pathinfo($photo->src, PATHINFO_EXTENSION);
                                                                @endphp
                                                                <div class="media-container">
                                                                    @if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif']))
                                                                        <div class="photo-item">
                                                                            <img src="{{ asset('storage/' . $photo->src) }}" class="img-fluid  rounded" alt="Image">
                                                                        </div>
                                                                    @elseif (in_array($extension, ['mp3', 'wav']))
                                                                        <audio controls>
                                                                            <source src="{{ asset('storage/' . $photo->src) }}" type="audio/{{ $extension }}">
                                                                            Your browser does not support the audio element.
                                                                        </audio>
                                                                    @elseif (in_array($extension, ['mp4', 'mov', 'avi', 'HIEC']))
                                                                        <div class="video-container video ">
                                                                            <video controls width="100%">
                                                                                <source src="{{ asset('storage/' . $photo->src) }}" type="video/{{ $extension }}">
                                                                                Your browser does not support the video element.
                                                                            </video>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <p class="no-media-text">No post photos</p>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="card-footer">
                                            <div class="row justify-content-between align-items-center">
                                                <div class="col-auto">
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-eye icon mr-2"></i>
                                                        <span class="text-secondary">{{ $servicePost->view_count }}</span>
                                                    </div>
                                                </div>
                                                <div class="col-auto">
                                                    <div class="d-flex align-items-center">
                                                        <a class="favorite-btn" data-post-id="{{ $servicePost->id }}">
                                                            <i class="fas {{ auth()->check() && $servicePost->isFavoritedBy(auth()->user()) ? 'text-danger' : 'text-muted' }} fa-heart icon mr-2"></i>
                                                            <span class="favorite-count">{{ $servicePost->favoritesCount() }}</span>
                                                        </a>
                                                    </div>
                                                </div>
                                                <div class="col-auto">
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-dollar-sign icon mr-2"></i>
                                                        <span class="text-primary">{{ $servicePost->price }} {{ $servicePost->price_currency }}</span>
                                                    </div>
                                                </div>
                                                <div class="col-auto">
                                                    <div class="dropdown">
                                                        <a href="#" class="text-secondary" role="button" id="reportDropdown" data-bs-toggle="dropdown"
                                                           aria-haspopup="true" aria-expanded="false">
                                                            <i class="fas fa-ellipsis-h"></i>
                                                        </a>
                                                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="reportDropdown">
                                                            <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#reportModal">
                                                                Report
                                                            </button>
                                                            <!-- Add more dropdown items if needed -->
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        $(document).ready(function () {
            $('.favorite-btn').on('click', function (e) {
                e.preventDefault();
                var postId = $(this).data('post-id');
                var favoriteBtn = $(this);
                var isFavorite = favoriteBtn.find('i').hasClass('text-danger');

                $.ajax({
                    url: '{{ route('favorites.store') }}',
                    method: 'POST',
                    data: {
                        post_id: postId,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (data) {
                        var count = data.count;
                        var isFavorited = data.is_favorited;

                        if (isFavorited) {
                            favoriteBtn.find('i').removeClass('text-muted').addClass('text-danger');
                        } else {
                            favoriteBtn.find('i').removeClass('text-danger').addClass('text-muted');
                        }

                        favoriteBtn.find('.favorite-count').text(count);
                    }
                });
            });

// Define a debounce function
            function debounce(func, wait, immediate) {
                var timeout;
                return function () {
                    var context = this, args = arguments;
                    var later = function () {
                        timeout = null;
                        if (!immediate) func.apply(context, args);
                    };
                    var callNow = immediate && !timeout;
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                    if (callNow) func.apply(context, args);
                };
            }

            // Use the debounce function to delay the execution of the code
            $(window).on('scroll', debounce(function () {
                $('.service-post').each(function () {
                    var servicePostElement = $(this).get(0);
                    var servicePostBottom = servicePostElement.offsetTop + servicePostElement.offsetHeight;
                    var windowHeight = window.innerHeight;
                    var windowBottom = window.scrollY + windowHeight;

                    if (windowBottom >= servicePostBottom) {
                        // Get the servicePost ID from the data attribute
                        var servicePostId = $(servicePostElement).data('service-post-id');

                        // Get the CSRF token from the page's meta tags
                        var csrfToken = $('meta[name="csrf-token"]').attr('content');

                        // Make an AJAX request to increment the view count for this service post
                        $.ajax({
                            url: '{{ route('inViewCount.view', ':id') }}'.replace(':id', servicePostId),
                            type: 'POST',
                            data: {
                                _token: csrfToken
                            },
                            success: function (response) {
                                console.log(response);
                            }
                        });
                    }
                });
            }, 500));

            $.each(categories, function (index, category) {
                var categoryBtn = $('<button>')
                    .addClass('category-btn btn btn-secondary')
                    .data('category', category.name)
                    .text(category.name + ' (' + category.service_posts_count + ')');
                categoryContainer.append(categoryBtn);
            });

            $('.category-btn').on('click', function () {
                alert('wadwad');
                var category = $(this).data('category');
                $.ajax({
                    url: '{{ route('sub-categories.category', ':category') }}'.replace(':category', category),
                    success: function (subCategories) {
                        var subcategoryContainer = $('.subcategory-container');
                        subcategoryContainer.empty();
                        $.each(subCategories, function (index, subCategory) {
                            var subcategoryBtn = $('<button>')
                                .addClass('subcategory-btn btn btn-secondary')
                                .data('subcategory', subCategory.name)
                                .text(subCategory.name + ' (' + subCategory.service_posts_count + ')');
                            subcategoryContainer.append(subcategoryBtn);
                        });
                    }
                });
            });

            $(document).on('click', '.subcategory-btn', function () {
                var subCategory = $(this).data('subcategory');
                $.ajax({
                    url: '{{ route('service-posts.category', ':category') }}'.replace(':category', subCategory),

                    success: function (servicePosts) {
                        // Update the service post container with the new data
                    }
                });
            });
        });
    </script>
@endsection
