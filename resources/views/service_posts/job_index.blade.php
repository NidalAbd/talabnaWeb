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
                    <a class="nav-link active" id="all-tab" data-toggle="tab" href="#all" role="tab">{{('service_posts\job_index.all') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="offers-tab" data-toggle="tab" href="#offers" role="tab">{{('service_posts\job_index.offers') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="requests-tab" data-toggle="tab" href="#requests" role="tab">{{('service_posts\job_index.requests') }}</a>
                </li>
            </ul>
            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="servicePostsTable">
                    <thead class="thead-light">
                        <tr>
                            <th>{{('service_posts\job_index.title') }}</th>
                            <th>{{('service_posts\job_index.category') }}</th>
                            <th>{{('service_posts\job_index.type') }}</th>
                            <th>{{('service_posts\job_index.price') }}</th>
                            <th>{{('service_posts\job_index.user') }}</th>
                            <th>{{('service_posts\job_index.status') }}</th>
                            <th>{{('service_posts\job_index.created') }}</th>
                            <th>{{('service_posts\job_index.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($servicePosts as $post)
                        <tr>
                            <td>{{ Str::limit($post->id) }}</td>
                            <td>{{ $post->id }}</td>
                            <td><span class="badge {{ $post->type == 'عرض' ? 'badge-info' : 'badge-secondary' }}">{{ $post->type == 'عرض' ? 'Offer' : 'Request' }}</span></td>
                            <td>{{ number_format($post->price, 0) }} {{ $post->price_currency_code }}</td>
                            <td>{{ $post->id }}</td>
                            <td><span class="badge {{ $post->state == 'published' ? 'badge-success' : 'badge-warning' }}">{{ ucfirst($post->state) }}</span></td>
                            <td>{{ $post->id }}</td>
                            <td>
                                <a href="{{ route('service_posts.show', $post->id) }}"><i class="fas fa-eye"></i></a>
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







