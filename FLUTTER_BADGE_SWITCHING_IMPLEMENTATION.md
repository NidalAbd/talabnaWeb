# Flutter App - Badge Switching Implementation Guide

## Overview
The backend now supports **badge switching with automatic refund** for unused days. This guide explains what needs to be updated in the Flutter app to support this feature.

---

## Backend Changes Summary

### 1. New API Endpoint

**Calculate Refund Preview**
```
POST /service_posts/{servicePostId}/calculate-refund-preview
Content-Type: application/json
Authorization: Bearer {token}

Request Body:
{
  "badge_type_id": 2,
  "days": 7
}

Response (200 OK):
{
  "has_current_badge": true,
  "current_badge": {
    "name_ar": "ماسي",
    "name_en": "Diamond"
  },
  "refund_info": {
    "used_days": 2,
    "remaining_days": 5,
    "refund_amount": 50,
    "total_paid": 70,
    "points_per_day": 10
  },
  "refund_amount": 50,
  "new_cost": 35,
  "net_amount": -15,
  "current_balance": 100,
  "new_balance": 115,
  "can_afford": true
}
```

### 2. Updated Apply Badge Endpoint Behavior

**Existing endpoint** (no changes needed to URL):
```
POST /service_posts/{servicePostId}/apply-badge
Content-Type: application/json
Authorization: Bearer {token}

Request Body:
{
  "badge_type_id": 2,
  "days": 7
}
```

**New behavior:**
- Automatically detects if switching from existing badge
- Calculates and applies refund for unused days
- Charges net amount (new cost - refund)
- Returns detailed success message with refund info

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Badge switched successfully! Refunded 50 points for 5 unused days."
}
```

**Response (422 Error - Insufficient Points):**
```json
{
  "success": false,
  "message": "Insufficient points. Need 50 points but you have 30"
}
```

### 3. New Notification Type

**Type:** `badge_switched`

**Example notification data:**
```json
{
  "type": "badge_switched",
  "title": "تم تغيير شارة الإعلان",
  "message": "تم تغيير الشارة من ماسي إلى ذهبي لمدة 7 يوم\nتم استرجاع 50 نقطة من الشارة السابقة\nتم خصم 15 نقطة",
  "data": {
    "service_post_id": 1067,
    "old_badge_id": 1,
    "old_badge_name": "ماسي",
    "new_badge_id": 2,
    "new_badge_name": "ذهبي",
    "days": 7,
    "refund_amount": 50,
    "net_amount": 15
  }
}
```

### 4. New Transaction Type

In `point_transactions` responses, you may now see:
- Type: `"refund"` - Points refunded from badge switching

---

## Flutter Implementation Required

### 1. Update Badge Application Screen

**Current behavior:**
- User selects badge type
- User enters duration
- Shows total cost
- User confirms and applies

**New behavior needed:**
- User selects badge type
- User enters duration
- **Call preview API to get refund info**
- Show detailed cost breakdown:
  - Total cost of new badge
  - Refund amount (if switching)
  - Remaining unused days
  - **Net amount** (highlighted - what user will actually pay/receive)
  - New balance after transaction
- Show different confirmation dialog for switching vs new badge
- Handle insufficient points error

**Example UI:**

```
┌─────────────────────────────────┐
│ Select Badge: [Diamond ▼]      │
│ Duration: [7] days              │
│                                 │
│ ┌─────────────────────────────┐ │
│ │ Cost Breakdown              │ │
│ │                             │ │
│ │ New Badge: 70 points        │ │
│ │ 💰 Refund: 50 points        │ │
│ │    (5 unused days)          │ │
│ │ ────────────────────        │ │
│ │ Net Charge: 20 points ✓     │ │
│ │                             │ │
│ │ Current: 100 pts            │ │
│ │ After: 80 pts               │ │
│ └─────────────────────────────┘ │
│                                 │
│ [Switch Badge]                  │
└─────────────────────────────────┘
```

### 2. Add Preview API Call

**Function to add in your API service:**

```dart
Future<BadgeRefundPreview> calculateRefundPreview({
  required int servicePostId,
  required int badgeTypeId,
  required int days,
}) async {
  final response = await http.post(
    Uri.parse('$baseUrl/service_posts/$servicePostId/calculate-refund-preview'),
    headers: {
      'Authorization': 'Bearer $token',
      'Content-Type': 'application/json',
    },
    body: json.encode({
      'badge_type_id': badgeTypeId,
      'days': days,
    }),
  );

  if (response.statusCode == 200) {
    return BadgeRefundPreview.fromJson(json.decode(response.body));
  } else {
    throw Exception('Failed to calculate refund preview');
  }
}
```

### 3. Create Model Classes

**BadgeRefundPreview Model:**

```dart
class BadgeRefundPreview {
  final bool hasCurrentBadge;
  final CurrentBadgeInfo? currentBadge;
  final RefundInfo? refundInfo;
  final int refundAmount;
  final int newCost;
  final int netAmount;
  final int currentBalance;
  final int newBalance;
  final bool canAfford;

  BadgeRefundPreview({
    required this.hasCurrentBadge,
    this.currentBadge,
    this.refundInfo,
    required this.refundAmount,
    required this.newCost,
    required this.netAmount,
    required this.currentBalance,
    required this.newBalance,
    required this.canAfford,
  });

  factory BadgeRefundPreview.fromJson(Map<String, dynamic> json) {
    return BadgeRefundPreview(
      hasCurrentBadge: json['has_current_badge'] ?? false,
      currentBadge: json['current_badge'] != null
          ? CurrentBadgeInfo.fromJson(json['current_badge'])
          : null,
      refundInfo: json['refund_info'] != null
          ? RefundInfo.fromJson(json['refund_info'])
          : null,
      refundAmount: json['refund_amount'] ?? 0,
      newCost: json['new_cost'] ?? 0,
      netAmount: json['net_amount'] ?? 0,
      currentBalance: json['current_balance'] ?? 0,
      newBalance: json['new_balance'] ?? 0,
      canAfford: json['can_afford'] ?? false,
    );
  }
}

class CurrentBadgeInfo {
  final String nameAr;
  final String nameEn;

  CurrentBadgeInfo({required this.nameAr, required this.nameEn});

  factory CurrentBadgeInfo.fromJson(Map<String, dynamic> json) {
    return CurrentBadgeInfo(
      nameAr: json['name_ar'] ?? '',
      nameEn: json['name_en'] ?? '',
    );
  }
}

class RefundInfo {
  final int usedDays;
  final int remainingDays;
  final int refundAmount;
  final int totalPaid;
  final int pointsPerDay;

  RefundInfo({
    required this.usedDays,
    required this.remainingDays,
    required this.refundAmount,
    required this.totalPaid,
    required this.pointsPerDay,
  });

  factory RefundInfo.fromJson(Map<String, dynamic> json) {
    return RefundInfo(
      usedDays: json['used_days'] ?? 0,
      remainingDays: json['remaining_days'] ?? 0,
      refundAmount: json['refund_amount'] ?? 0,
      totalPaid: json['total_paid'] ?? 0,
      pointsPerDay: json['points_per_day'] ?? 0,
    );
  }
}
```

### 4. Update Apply Badge Logic

**Before submitting:**

```dart
// Get preview first
final preview = await calculateRefundPreview(
  servicePostId: post.id,
  badgeTypeId: selectedBadgeId,
  days: durationDays,
);

// Check if can afford
if (!preview.canAfford) {
  showErrorDialog(
    'رصيد غير كافي',
    'تحتاج إلى ${preview.netAmount} نقطة ولكن لديك ${preview.currentBalance} نقطة فقط',
  );
  return;
}

// Show confirmation with details
final confirmed = await showConfirmationDialog(
  title: preview.hasCurrentBadge ? 'تأكيد تغيير الشارة' : 'تأكيد تطبيق الشارة',
  message: _buildConfirmationMessage(preview),
);

if (confirmed) {
  // Apply badge
  await applyBadge(
    servicePostId: post.id,
    badgeTypeId: selectedBadgeId,
    days: durationDays,
  );

  // Show success message
  showSuccessSnackbar(response.message);

  // Refresh post and points balance
  await refreshPost();
  await refreshUserPoints();
}
```

**Confirmation message builder:**

```dart
String _buildConfirmationMessage(BadgeRefundPreview preview) {
  if (preview.hasCurrentBadge && preview.refundAmount > 0) {
    // Switching with refund
    return '''
الشارة الجديدة: ${selectedBadge.nameAr}
المدة: ${durationDays} يوم

التكلفة: ${preview.newCost} نقطة
💰 استرجاع: ${preview.refundAmount} نقطة (${preview.refundInfo?.remainingDays} يوم غير مستخدم)
────────────────
صافي الخصم: ${preview.netAmount} نقطة

رصيدك الحالي: ${preview.currentBalance} نقطة
رصيدك بعد التطبيق: ${preview.newBalance} نقطة

هل تريد المتابعة؟
    ''';
  } else {
    // New badge application
    return '''
الشارة: ${selectedBadge.nameAr}
المدة: ${durationDays} يوم
التكلفة: ${preview.newCost} نقطة

رصيدك الحالي: ${preview.currentBalance} نقطة
رصيدك بعد التطبيق: ${preview.newBalance} نقطة

هل تريد المتابعة؟
    ''';
  }
}
```

### 5. Handle New Notification Type

**In notification handler:**

```dart
void handleNotification(NotificationModel notification) {
  switch (notification.type) {
    case 'badge_applied':
      // Existing handler
      break;

    case 'badge_switched':
      // New handler
      final data = notification.data;
      showNotificationDialog(
        title: notification.title,
        message: notification.message,
        onTap: () {
          // Navigate to service post
          navigateToPost(data['service_post_id']);
        },
      );
      break;

    case 'badge_expired':
      // Existing handler
      break;
  }
}
```

### 6. Update Points Transaction List

Add icon/label for refund type:

```dart
Widget _buildTransactionIcon(String type) {
  switch (type) {
    case 'purchase':
      return Icon(Icons.add_circle, color: Colors.green);
    case 'used':
      return Icon(Icons.remove_circle, color: Colors.red);
    case 'refund':
      return Icon(Icons.replay, color: Colors.blue); // NEW
    case 'transfer':
      return Icon(Icons.swap_horiz, color: Colors.orange);
    case 'admin_grant':
      return Icon(Icons.card_giftcard, color: Colors.purple);
    default:
      return Icon(Icons.circle, color: Colors.grey);
  }
}

String _getTransactionLabel(String type) {
  switch (type) {
    case 'purchase': return 'شراء نقاط';
    case 'used': return 'استخدام نقاط';
    case 'refund': return 'استرجاع نقاط'; // NEW
    case 'transfer': return 'تحويل نقاط';
    case 'admin_grant': return 'منحة من الإدارة';
    default: return type;
  }
}
```

---

## Business Logic Summary

### Refund Policy: Fair Refund for Unused Days

When user switches badges:
1. **Calculate used days** - How many days already consumed
2. **Calculate remaining days** - Days left until expiration
3. **Refund unused days** - User gets points back for unused days
4. **Charge new badge** - User pays for new badge
5. **Net transaction** - System calculates net amount (new cost - refund)

### Example Scenarios

**Scenario 1: Switch Diamond (10 pts/day) to Gold (5 pts/day)**
- Original: 7 days Diamond = 70 points paid
- Used: 2 days
- Remaining: 5 days
- Refund: 5 × 10 = 50 points
- New cost: 7 × 5 = 35 points
- **Net: 35 - 50 = -15 points (user gets 15 points back!)**

**Scenario 2: Upgrade Gold (5 pts/day) to Diamond (10 pts/day)**
- Original: 7 days Gold = 35 points paid
- Used: 3 days
- Remaining: 4 days
- Refund: 4 × 5 = 20 points
- New cost: 7 × 10 = 70 points
- **Net: 70 - 20 = 50 points charged**

---

## Testing Checklist

- [ ] Preview API returns correct refund calculation
- [ ] UI shows refund info when switching badges
- [ ] UI shows simple cost when applying first badge
- [ ] Confirmation dialog shows correct message for switching
- [ ] Confirmation dialog shows correct message for new badge
- [ ] Apply badge works and updates post
- [ ] Points balance refreshes after applying badge
- [ ] Insufficient points error is handled gracefully
- [ ] Success message displays with refund details
- [ ] Notification for badge_switched type displays correctly
- [ ] Transaction history shows refund entries
- [ ] Net negative amount (user gets points back) displays correctly

---

## API Base URL

Make sure your Flutter app is using the correct base URL for API calls:
```
https://your-domain.com/api
```

Or for local testing:
```
http://your-local-ip:8000/api
```

---

## Questions?

If you need clarification on any endpoint or behavior, test the web admin panel first to see how it works, then implement the same logic in Flutter.

**Test User:**
- Can create test posts
- Can apply/switch badges
- Can see refund calculations
- All behavior should match between web and mobile
