@extends('welcome')
@section('title', "User Posts")
@section('contentPost')
    <div class="container">
        @if(count($servicePosts) > 0)
            @foreach($servicePosts as $servicePost)
                        <div>
                            <div>
                                    <span class="position-sticky start-400  badge rounded-pill bg-info">
                                          <span class=""  data-service-post-id="{{ $servicePost->id }}">{{ $servicePost->category }}</span>
                                      </span>
                                <span class="position-sticky badge rounded-pill bg-info">
                                           <span class="">{{ $servicePost->sub_category }}</span>
                                    </span>
                            </div>
                        </div>
                        <div class="card ">
                            <div class="card-header
    @if($servicePost->have_badge == 'عادي')
        bg-light
    @elseif($servicePost->have_badge == 'ذهبي')
        bg-gradient-dark
    @elseif($servicePost->have_badge == 'ماسي')
        bg-gray-dark
    @endif" >
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <div class="modal fade" id="reportUserModal" role="dialog" aria-labelledby="reportUserModalLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 style="color: #1b1e21;" class="modal-title"
                                                            id="reportUserModalLabel">Report User</h5>
                                                        <button type="button" class="btn btn-close"
                                                                data-bs-dismiss="modal" aria-label="Close"><i
                                                                class="fa fa-times" aria-hidden="true"></i></button>
                                                    </div>
                                                    <form method="POST"
                                                          action="{{ route('reports.store', ['reported' => 'user', 'reportedId' => $user->id ?? 1010101010]) }}">
                                                        @csrf
                                                        <div class="modal-body">
                                                            <div class="form-group">
                                                                <label style="color: #1b1e21;" for="reason">Reason for
                                                                    report</label>
                                                                <select class="form-control" name="reason" id="reason">
                                                                    <option value="Spam">Spam</option>
                                                                    <option value="Inappropriate content">Inappropriate
                                                                        Content
                                                                    </option>
                                                                    <option value="Harassment">Harassment</option>
                                                                    <option value="False information">False
                                                                        information
                                                                    </option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary"
                                                                    data-bs-dismiss="modal">Cancel
                                                            </button>
                                                            <button type="submit" class="btn btn-danger">Submit Report
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="btn-group">
                                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                            </button>
                                            <ul class="dropdown-menu" style="">
                                                <li>
                                                    <button type="button" class="btn btn-default" data-bs-toggle="modal" data-bs-target="#reportUserModal">
                                                        Report User
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>
                                        <div>
                                            @if (auth()->check() && $servicePost->user->photos->count() > 0)
                                                <div class="user-image">
                                                    <img src="{{ asset('storage/' . $servicePost->user->photos->first()->src) }}" class="img-circle" alt="Profile Picture" width="50" height="60">
                                                </div>
                                            @else
                                                <div class="user-image">
                                                    <img src="{{ asset('storage/photos/avatar1.png') }}" class="img-circle" alt="Profile Picture" width="50" height="50">
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            @if (auth()->check())
                                                <h4>&nbsp;{{ $servicePost->user->user_name }}</h4>
                                            @else
                                                <h4>&nbsp;Anonymous User</h4>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="d-flex">
                                        <h5>{{ $servicePost->title}}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <p>{{ $servicePost->description }}</p>
                                <div class="media-container mt-4">
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
                <div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel"
                     aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="reportModalLabel">Report Service Post</h5>
                                <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close">
                                    <i class="fa fa-times" aria-hidden="true"></i></button>
                            </div>
                            <form method="POST"
                                  action="{{ route('reports.store', ['reported' => 'service_post', 'reportedId' => $servicePost->id]) }}">
                                @csrf
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label for="reason">Reason for report</label>
                                        <select class="form-control" name="reason" id="reason">
                                            <option value="spam">Spam</option>
                                            <option value="inappropriate content">Inappropriate Content</option>
                                            <option value="harassment">Harassment</option>
                                            <option value="false information">False information</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel
                                    </button>
                                    <button type="submit" class="btn btn-danger">Submit Report</button>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>

            @endforeach
        @else
            <div class="row justify-content-center">
                <div class="col-md-12 mb-3">
                    <div class="card ">
                        <div class="card-header">
                            <div class="row justify-content-between">
                                <div class="d-flex align-items-center">
                                    <div>
                                        @if ($user->photos->count() > 0)
                                            <img src="{{ asset('storage/' . $user->photos->first()->src) }}"
                                                 class="img-circle" alt="Profile Picture" width="50">
                                        @else
                                            <img src="{{ asset('storage/photos/avatar1.png') }}" class="img-circle"
                                                 alt="Profile Picture" width="50">
                                        @endif
                                    </div>
                                    <div>

                                        <h4>{{ $user->user_name }}</h4>
                                    </div>

                                </div>
                                <div class="d-flex align-items-center">
                                </div>
                            </div>


                        </div>
                        <div class="card-body text-right d-flex justify-content-center" style="height: 257px">
                            <p><strong>{{ 'no records' }}</strong></p>
                            <div>
                            </div>
                        </div>
                        <div class="card-footer" style="height: 50px">
                            <div class="row justify-content-between">
                                <div>

                                </div>
                                <div>

                                </div>
                                <div>

                                </div>
                                <div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
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
