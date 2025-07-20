@extends('adminlte::page')
@section('title', 'Job Service Posts')
@section('content')
<div class="container-fluid py-4">
    <div class="card mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="fas fa-briefcase text-primary mr-2"></i> Job Service Posts</h4>
        </div>
        <div class="card-body">
            <!-- Filter Tabs -->
            <ul class="nav nav-tabs mb-3" id="filterTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="all-tab" data-toggle="tab" href="#all" role="tab">All</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="offers-tab" data-toggle="tab" href="#offers" role="tab">Offers</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="requests-tab" data-toggle="tab" href="#requests" role="tab">Requests</a>
                </li>
            </ul>
            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="servicePostsTable">
                    <thead class="thead-light">
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Type</th>
                            <th>Price</th>
                            <th>User</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($servicePosts as $post)
                        <tr>
                            <td>{{ Str::limit($post->title, 40) }}</td>
                            <td>{{ $post->category->name[app()->getLocale()] ?? $post->category->name['en'] ?? 'Unknown' }}</td>
                            <td><span class="badge {{ $post->type == 'عرض' ? 'badge-info' : 'badge-secondary' }}">{{ $post->type == 'عرض' ? 'Offer' : 'Request' }}</span></td>
                            <td>{{ number_format($post->price, 0) }} {{ $post->price_currency_code }}</td>
                            <td>{{ $post->user->user_name ?? 'N/A' }}</td>
                            <td><span class="badge {{ $post->state == 'published' ? 'badge-success' : 'badge-warning' }}">{{ ucfirst($post->state) }}</span></td>
                            <td>{{ $post->created_at->diffForHumans() }}</td>
                            <td>
                                <a href="{{ route('service_posts.show', $post->id) }}" class="btn btn-sm btn-primary"><i class="fas fa-eye"></i></a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $servicePosts->links() }}
            </div>
        </div>
    </div>
</div>
@stop
