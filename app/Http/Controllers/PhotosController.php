<?php

namespace App\Http\Controllers;

use App\Models\Photos;
use App\Http\Requests\StorephotosRequest;
use App\Http\Requests\UpdatephotosRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

class PhotosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorephotosRequest $request): RedirectResponse
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Photos $photos): Response
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Photos $photos): Response
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatephotosRequest $request, Photos $photos): RedirectResponse
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Photos $photos): RedirectResponse
    {
        //
    }
}
