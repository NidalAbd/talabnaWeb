# Flutter: Display Featured/Popular Badges for Subcategories

## 1. Update SubCategory Model

```dart
class SubCategory {
  final int id;
  final String name;
  final int categoriesId;
  final bool isSuspended;
  final bool isFeatured;    // NEW
  final bool isPopular;     // NEW
  final int servicePostsCount;
  final List<Photo>? photos;

  SubCategory({
    required this.id,
    required this.name,
    required this.categoriesId,
    required this.isSuspended,
    required this.isFeatured,     // NEW
    required this.isPopular,      // NEW
    required this.servicePostsCount,
    this.photos,
  });

  factory SubCategory.fromJson(Map<String, dynamic> json) {
    return SubCategory(
      id: json['id'],
      name: json['name'],
      categoriesId: json['categories_id'],
      isSuspended: json['isSuspended'] == 1,
      isFeatured: json['is_featured'] == 1,    // NEW
      isPopular: json['is_popular'] == 1,      // NEW
      servicePostsCount: json['service_posts_count'] ?? 0,
      photos: json['photos'] != null
        ? (json['photos'] as List).map((p) => Photo.fromJson(p)).toList()
        : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'categories_id': categoriesId,
      'isSuspended': isSuspended ? 1 : 0,
      'is_featured': isFeatured ? 1 : 0,      // NEW
      'is_popular': isPopular ? 1 : 0,        // NEW
      'service_posts_count': servicePostsCount,
      'photos': photos?.map((p) => p.toJson()).toList(),
    };
  }
}
```

## 2. Update Category Model (same way)

```dart
class Category {
  final int id;
  final String name;
  final bool isSuspended;
  final bool isFeatured;    // NEW
  final bool isPopular;     // NEW
  // ... other fields

  // Update fromJson and toJson accordingly
}
```

## 3. Display Badge in SubCategory List Widget

```dart
class SubCategoryCard extends StatelessWidget {
  final SubCategory subcategory;

  const SubCategoryCard({Key? key, required this.subcategory}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Stack(
        children: [
          // Main content
          ListTile(
            leading: subcategory.photos?.isNotEmpty == true
                ? Image.network(subcategory.photos!.first.url)
                : Icon(Icons.category),
            title: Text(subcategory.name),
            subtitle: Text('${subcategory.servicePostsCount} services'),
          ),

          // Badge overlay (top-right)
          if (subcategory.isFeatured || subcategory.isPopular)
            Positioned(
              top: 8,
              right: 8,
              child: _buildBadge(),
            ),
        ],
      ),
    );
  }

  Widget _buildBadge() {
    // Priority: Featured > Popular
    if (subcategory.isFeatured) {
      return Container(
        padding: EdgeInsets.symmetric(horizontal: 8, vertical: 4),
        decoration: BoxDecoration(
          color: Colors.purple,
          borderRadius: BorderRadius.circular(12),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(Icons.star, size: 14, color: Colors.white),
            SizedBox(width: 4),
            Text(
              'Featured',
              style: TextStyle(
                color: Colors.white,
                fontSize: 12,
                fontWeight: FontWeight.bold,
              ),
            ),
          ],
        ),
      );
    } else if (subcategory.isPopular) {
      return Container(
        padding: EdgeInsets.symmetric(horizontal: 8, vertical: 4),
        decoration: BoxDecoration(
          color: Colors.orange,
          borderRadius: BorderRadius.circular(12),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(Icons.trending_up, size: 14, color: Colors.white),
            SizedBox(width: 4),
            Text(
              'Popular',
              style: TextStyle(
                color: Colors.white,
                fontSize: 12,
                fontWeight: FontWeight.bold,
              ),
            ),
          ],
        ),
      );
    }
    return SizedBox.shrink();
  }
}
```

## 4. Offline Support with Hive/SQLite

### Option A: Using Hive (Recommended)

```dart
import 'package:hive/hive.dart';

// 1. Update HiveType annotation
@HiveType(typeId: 2)
class SubCategory extends HiveObject {
  @HiveField(0)
  final int id;

  @HiveField(1)
  final String name;

  @HiveField(2)
  final int categoriesId;

  @HiveField(3)
  final bool isSuspended;

  @HiveField(4)  // NEW FIELD
  final bool isFeatured;

  @HiveField(5)  // NEW FIELD
  final bool isPopular;

  @HiveField(6)
  final int servicePostsCount;

  // ... constructor and methods
}

// 2. Remember to regenerate Hive adapters
// Run: flutter packages pub run build_runner build --delete-conflicting-outputs
```

### Option B: Using SQLite

```dart
// Update database schema
class DatabaseHelper {
  static const String createSubCategoryTable = '''
    CREATE TABLE subcategories (
      id INTEGER PRIMARY KEY,
      name TEXT NOT NULL,
      categories_id INTEGER NOT NULL,
      isSuspended INTEGER DEFAULT 0,
      is_featured INTEGER DEFAULT 0,  -- NEW
      is_popular INTEGER DEFAULT 0,   -- NEW
      service_posts_count INTEGER DEFAULT 0,
      created_at TEXT,
      updated_at TEXT
    )
  ''';

  // Add migration for existing databases
  Future<void> _onUpgrade(Database db, int oldVersion, int newVersion) async {
    if (oldVersion < 2) {
      // Add new columns if upgrading from version 1
      await db.execute('ALTER TABLE subcategories ADD COLUMN is_featured INTEGER DEFAULT 0');
      await db.execute('ALTER TABLE subcategories ADD COLUMN is_popular INTEGER DEFAULT 0');
      await db.execute('ALTER TABLE categories ADD COLUMN is_featured INTEGER DEFAULT 0');
      await db.execute('ALTER TABLE categories ADD COLUMN is_popular INTEGER DEFAULT 0');
    }
  }
}
```

## 5. Data Sync Strategy

```dart
class SubCategoryRepository {
  final ApiService _api;
  final Box<SubCategory> _hiveBox;  // or DatabaseHelper for SQLite

  SubCategoryRepository(this._api, this._hiveBox);

  // Fetch and cache
  Future<List<SubCategory>> getSubCategories(int categoryId, {bool forceRefresh = false}) async {
    try {
      // Try to fetch from API
      final response = await _api.getSubCategories(categoryId);
      final subcategories = response.map((json) => SubCategory.fromJson(json)).toList();

      // Save to offline storage
      await _saveToCache(subcategories);

      return subcategories;
    } catch (e) {
      // If offline or error, return cached data
      return _getFromCache(categoryId);
    }
  }

  Future<void> _saveToCache(List<SubCategory> subcategories) async {
    for (var subcat in subcategories) {
      await _hiveBox.put(subcat.id, subcat);
    }
  }

  List<SubCategory> _getFromCache(int categoryId) {
    return _hiveBox.values
        .where((s) => s.categoriesId == categoryId)
        .toList();
  }

  // Get featured/popular from cache
  List<SubCategory> getFeaturedFromCache() {
    return _hiveBox.values.where((s) => s.isFeatured).toList();
  }

  List<SubCategory> getPopularFromCache() {
    return _hiveBox.values.where((s) => s.isPopular).toList();
  }
}
```

## 6. UI Implementation Example

```dart
class SubCategoryListScreen extends StatelessWidget {
  final int categoryId;
  final SubCategoryRepository repository;

  const SubCategoryListScreen({
    Key? key,
    required this.categoryId,
    required this.repository,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return FutureBuilder<List<SubCategory>>(
      future: repository.getSubCategories(categoryId),
      builder: (context, snapshot) {
        if (snapshot.connectionState == ConnectionState.waiting) {
          // Try to show cached data while loading
          final cached = repository._getFromCache(categoryId);
          if (cached.isNotEmpty) {
            return _buildList(cached, isOffline: true);
          }
          return CircularProgressIndicator();
        }

        if (snapshot.hasError) {
          // Show cached data on error
          final cached = repository._getFromCache(categoryId);
          if (cached.isNotEmpty) {
            return Column(
              children: [
                // Offline indicator
                Container(
                  padding: EdgeInsets.all(8),
                  color: Colors.amber.shade100,
                  child: Row(
                    children: [
                      Icon(Icons.cloud_off, size: 16),
                      SizedBox(width: 8),
                      Text('Showing offline data'),
                    ],
                  ),
                ),
                Expanded(child: _buildList(cached, isOffline: true)),
              ],
            );
          }
          return ErrorWidget(snapshot.error!);
        }

        return _buildList(snapshot.data!, isOffline: false);
      },
    );
  }

  Widget _buildList(List<SubCategory> subcategories, {required bool isOffline}) {
    // Sort: Featured first, then popular, then others
    final sorted = [...subcategories]..sort((a, b) {
      if (a.isFeatured && !b.isFeatured) return -1;
      if (!a.isFeatured && b.isFeatured) return 1;
      if (a.isPopular && !b.isPopular) return -1;
      if (!a.isPopular && b.isPopular) return 1;
      return b.servicePostsCount.compareTo(a.servicePostsCount);
    });

    return ListView.builder(
      itemCount: sorted.length,
      itemBuilder: (context, index) {
        return SubCategoryCard(subcategory: sorted[index]);
      },
    );
  }
}
```

## 7. API Endpoints to Use

```dart
class ApiService {
  final Dio _dio;

  // Get all subcategories for a category (includes is_featured, is_popular)
  Future<List<Map<String, dynamic>>> getSubCategories(int categoryId) async {
    final response = await _dio.get('/api/public/CategoriesSelected/$categoryId');
    return List<Map<String, dynamic>>.from(response.data['subcategories']);
  }

  // Get only featured subcategories
  Future<List<Map<String, dynamic>>> getFeaturedSubCategories() async {
    final response = await _dio.get('/api/public/subcategories_featured');
    return List<Map<String, dynamic>>.from(response.data['subcategories']);
  }

  // Get only popular subcategories
  Future<List<Map<String, dynamic>>> getPopularSubCategories() async {
    final response = await _dio.get('/api/public/subcategories_popular');
    return List<Map<String, dynamic>>.from(response.data['subcategories']);
  }
}
```

## Summary

### Updates Needed in Flutter App:

1. ✅ **Models**: Add `isFeatured` and `isPopular` boolean fields to Category and SubCategory models
2. ✅ **Database**: Add migrations for Hive or SQLite to include new fields
3. ✅ **UI**: Add badge display in list items (Featured = purple star, Popular = orange trending icon)
4. ✅ **Sorting**: Sort lists to show Featured first, then Popular, then regular
5. ✅ **Offline**: Store badges in local cache so they display even offline
6. ✅ **Sync**: When online, fetch fresh data and update cache

### Badge Display Priority:
1. **Featured** (Purple with star icon) - Highest priority
2. **Popular** (Orange with trending icon) - Second priority
3. **Regular** (No badge) - Default

The badges will work offline because they're stored in the model and cached locally!
