# Universal AJAX Handler Usage Examples

This document shows how to use the universal AJAX handler for all your Laravel admin panel operations.

## 1. Service Posts Operations

### Delete Service Post
```html
<form class="ajax-form" data-url="{{ route('service_posts.destroy', $post->id) }}" data-method="DELETE" 
      data-confirm="Are you sure you want to delete this service post?" 
      data-success="Service post deleted successfully!" style="display: inline;">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-outline-danger"><i class="fas fa-trash"></i></button>
</form>
```

### Approve Service Post
```html
<form class="ajax-form" data-url="{{ route('service_posts.approve', $post->id) }}" data-method="PATCH" 
      data-confirm="Are you sure you want to approve this service post?" 
      data-success="Service post approved successfully!" style="display: inline;">
    @csrf
    @method('PATCH')
    <button type="submit" class="btn btn-outline-success"><i class="fas fa-check"></i></button>
</form>
```

### Reject Service Post
```html
<form class="ajax-form" data-url="{{ route('service_posts.reject', $post->id) }}" data-method="PATCH" 
      data-confirm="Are you sure you want to reject this service post?" 
      data-success="Service post rejected successfully!" style="display: inline;">
    @csrf
    @method('PATCH')
    <button type="submit" class="btn btn-outline-warning"><i class="fas fa-times"></i></button>
</form>
```

### Toggle Premium Status
```html
<form class="ajax-form" data-url="{{ route('service_posts.toggle-premium', $post->id) }}" data-method="PATCH" 
      data-confirm="Are you sure you want to toggle the premium status?" 
      data-success="Premium status updated successfully!" style="display: inline;">
    @csrf
    <button type="submit" class="btn btn-outline-warning"><i class="fas fa-star"></i></button>
</form>
```

## 2. User Management Operations

### Ban User
```html
<form class="ajax-form" data-url="{{ route('users.ban', $user->id) }}" data-method="POST" 
      data-confirm="Are you sure you want to ban this user?" 
      data-success="User banned successfully!" style="display: inline;">
    @csrf
    <button type="submit" class="btn btn-outline-danger"><i class="fas fa-ban"></i></button>
</form>
```

### Unban User
```html
<form class="ajax-form" data-url="{{ route('users.unban', $user->id) }}" data-method="POST" 
      data-confirm="Are you sure you want to unban this user?" 
      data-success="User unbanned successfully!" style="display: inline;">
    @csrf
    <button type="submit" class="btn btn-outline-success"><i class="fas fa-user-check"></i></button>
</form>
```

### Toggle Ban Status
```html
<form class="ajax-form" data-url="{{ route('users.toggle-ban', $user->id) }}" data-method="POST" 
      data-confirm="Are you sure you want to toggle the ban status?" 
      data-success="User status updated successfully!" style="display: inline;">
    @csrf
    <button type="submit" class="btn btn-outline-warning"><i class="fas fa-user-slash"></i></button>
</form>
```

## 3. Category Management

### Toggle Category Suspend
```html
<form class="ajax-form" data-url="{{ route('categories.toggle-suspend', $category->id) }}" data-method="PUT" 
      data-confirm="Are you sure you want to toggle the suspend status?" 
      data-success="Category status updated successfully!" style="display: inline;">
    @csrf
    @method('PUT')
    <button type="submit" class="btn btn-outline-warning"><i class="fas fa-pause"></i></button>
</form>
```

### Delete Category
```html
<form class="ajax-form" data-url="{{ route('categories.destroy', $category->id) }}" data-method="DELETE" 
      data-confirm="Are you sure you want to delete this category?" 
      data-success="Category deleted successfully!" style="display: inline;">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-outline-danger"><i class="fas fa-trash"></i></button>
</form>
```

## 4. Subcategory Management

### Toggle Subcategory Suspend
```html
<form class="ajax-form" data-url="{{ route('subcategories.toggle-suspend', $subcategory->id) }}" data-method="PUT" 
      data-confirm="Are you sure you want to toggle the suspend status?" 
      data-success="Subcategory status updated successfully!" style="display: inline;">
    @csrf
    @method('PUT')
    <button type="submit" class="btn btn-outline-warning"><i class="fas fa-pause"></i></button>
</form>
```

## 5. Point Package Management

### Update Point Package
```html
<form class="ajax-form" data-url="{{ route('admin.point_packages.update', $package->id) }}" data-method="PUT" 
      data-confirm="Are you sure you want to update this package?" 
      data-success="Package updated successfully!" style="display: inline;">
    @csrf
    @method('PUT')
    <button type="submit" class="btn btn-outline-primary"><i class="fas fa-save"></i></button>
</form>
```

### Delete Point Package
```html
<form class="ajax-form" data-url="{{ route('admin.point_packages.destroy', $package->id) }}" data-method="DELETE" 
      data-confirm="Are you sure you want to delete this package?" 
      data-success="Package deleted successfully!" style="display: inline;">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-outline-danger"><i class="fas fa-trash"></i></button>
</form>
```

## 6. Purchase Requests Management

### Approve Purchase Request
```html
<form class="ajax-form" data-url="{{ route('purchase_points.approved', $request->id) }}" data-method="PUT" 
      data-confirm="Are you sure you want to approve this purchase request?" 
      data-success="Purchase request approved successfully!" style="display: inline;">
    @csrf
    @method('PUT')
    <button type="submit" class="btn btn-outline-success"><i class="fas fa-check"></i></button>
</form>
```

### Cancel Purchase Request
```html
<form class="ajax-form" data-url="{{ route('purchase_points.cancel', $request->id) }}" data-method="PUT" 
      data-confirm="Are you sure you want to cancel this purchase request?" 
      data-success="Purchase request cancelled successfully!" style="display: inline;">
    @csrf
    @method('PUT')
    <button type="submit" class="btn btn-outline-warning"><i class="fas fa-times"></i></button>
</form>
```

## 7. Report Management

### Delete Report
```html
<form class="ajax-form" data-url="{{ route('reports.destroy', $report->id) }}" data-method="DELETE" 
      data-confirm="Are you sure you want to delete this report?" 
      data-success="Report deleted successfully!" style="display: inline;">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-outline-danger"><i class="fas fa-trash"></i></button>
</form>
```

## 8. Using AJAX Buttons (Alternative Method)

Instead of forms, you can use buttons with data attributes:

```html
<button class="btn btn-outline-danger ajax-btn" 
        data-url="{{ route('service_posts.destroy', $post->id) }}" 
        data-method="DELETE"
        data-confirm="Are you sure you want to delete this service post?"
        data-success="Service post deleted successfully!">
    <i class="fas fa-trash"></i>
</button>
```

## 9. Using AJAX Links (Alternative Method)

You can also use links with data attributes:

```html
<a href="{{ route('service_posts.destroy', $post->id) }}" 
   class="btn btn-outline-danger ajax-link"
   data-method="DELETE"
   data-confirm="Are you sure you want to delete this service post?"
   data-success="Service post deleted successfully!">
    <i class="fas fa-trash"></i>
</a>
```

## 10. Manual AJAX Requests (JavaScript)

For custom operations, you can use the handler directly:

```javascript
// Simple request
ajaxHandler.request('/api/users/123/ban', 'POST', {}, {
    successMessage: 'User banned successfully!',
    confirmMessage: 'Are you sure you want to ban this user?'
});

// With custom data
ajaxHandler.request('/api/service-posts/456/toggle-premium', 'PATCH', {
    is_premium: true
}, {
    successMessage: 'Premium status updated!',
    confirmMessage: 'Toggle premium status?'
});

// Without confirmation
ajaxHandler.request('/api/categories/789/refresh', 'GET', {}, {
    successMessage: 'Categories refreshed!'
});
```

## 11. Advanced Options

### Prevent Page Reload
```html
<form class="ajax-form" data-url="{{ route('service_posts.toggle-premium', $post->id) }}" 
      data-method="PATCH" 
      data-reload="false"
      data-success="Premium status updated successfully!" 
      style="display: inline;">
    @csrf
    <button type="submit" class="btn btn-outline-warning"><i class="fas fa-star"></i></button>
</form>
```

### Custom Success Message
```html
<form class="ajax-form" data-url="{{ route('users.ban', $user->id) }}" 
      data-method="POST" 
      data-success="User {{ $user->name }} has been banned successfully!" 
      style="display: inline;">
    @csrf
    <button type="submit" class="btn btn-outline-danger"><i class="fas fa-ban"></i></button>
</form>
```

## 12. Controller Response Format

All your controllers should return JSON responses like this:

```php
public function destroy(ServicePost $servicePost)
{
    try {
        $servicePost->delete();
        return response()->json(['success' => true, 'message' => 'Service post deleted successfully']);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => 'Failed to delete service post'], 500);
    }
}
```

## 13. Error Handling

The handler automatically handles different types of errors:

- **403 Unauthorized**: Shows "Unauthorized" message
- **404 Not Found**: Shows "Resource not found" message  
- **422 Validation Error**: Shows validation error messages
- **500 Server Error**: Shows "Operation failed" message

## 14. Features

✅ **Universal**: Works with all HTTP methods (GET, POST, PUT, PATCH, DELETE)  
✅ **Confirmation**: Optional SweetAlert2 confirmation dialogs  
✅ **Success Messages**: Customizable success notifications  
✅ **Error Handling**: Comprehensive error message display  
✅ **CSRF Protection**: Automatic CSRF token inclusion  
✅ **Page Reload**: Automatic page refresh after success (configurable)  
✅ **Multiple Formats**: Forms, buttons, and links supported  
✅ **Manual Control**: Direct JavaScript API for custom operations  

This universal AJAX handler will work with **ALL** your existing routes and any new routes you add to your Laravel admin panel! 