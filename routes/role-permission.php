Route::group(['middleware' => ['auth', 'admin']], function() {
// Role routes
Route::resource('roles', CustomRolesController::class);
Route::post('roles/{role}/clone', [CustomRolesController::class, 'clone'])->name('roles.clone');

// Permission routes
Route::resource('permissions', CustomPermissionsController::class);
Route::post('permissions/generate', [CustomPermissionsController::class, 'generateForModule'])->name('permissions.generate');

// Role Assignments
Route::get('role-assignments', [UserRoleAssignmentController::class, 'index'])->name('role-assignments.index');
Route::get('role-assignments/{id}/edit', [UserRoleAssignmentController::class, 'edit'])->name('role-assignments.edit');
Route::put('role-assignments/{id}', [UserRoleAssignmentController::class, 'update'])->name('role-assignments.update');
Route::get('role-assignments/role/{roleId}', [UserRoleAssignmentController::class, 'usersWithRole'])->name('role-assignments.users-with-role');
});
