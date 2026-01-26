# Notification System Refactoring Documentation

## Overview
The notification system has been refactored to ensure proper connections between admin side, backend, and database with improved architecture, error handling, and data integrity.

## Architecture

### 1. Repository Pattern (`NotificationRepository`)
- **Location**: `backend/app/Repositories/NotificationRepository.php`
- **Purpose**: Centralizes all database operations for notifications
- **Features**:
  - User notification operations (CRUD, read/unread)
  - Admin notification operations (CRUD, read/unread, dismiss)
  - Database transactions for data integrity
  - Proper error handling and logging
  - Query optimization with proper indexing

### 2. Transformer Pattern (`NotificationTransformer`)
- **Location**: `backend/app/Transformers/NotificationTransformer.php`
- **Purpose**: Ensures consistent API response format between admin and client
- **Features**:
  - Transforms user notifications to API format
  - Transforms admin notifications to API format
  - Handles collection transformations
  - Consistent field mapping

### 3. Service Layer
- **UserNotificationService**: Handles user-specific notifications
- **AdminNotificationService**: Handles admin-specific notifications
- **NotificationManager**: Coordinates between user and admin services
  - Ensures both sides are notified when appropriate
  - Uses database transactions for consistency
  - Handles bulk notifications efficiently

### 4. Controllers
- **Client/NotificationController**: Refactored to use Repository pattern
- **Admin/NotificationController**: Refactored to use Repository pattern
- Both controllers now:
  - Use dependency injection
  - Have proper error handling
  - Return consistent API responses
  - Use transformers for data formatting

## Database Structure

### Tables

#### 1. `notifications` (Laravel's built-in table)
- Stores user notifications
- Uses polymorphic relationship
- Indexed on `notifiable_type`, `notifiable_id`, `read_at`

#### 2. `admin_notifications`
- Stores admin-specific notifications
- Fields:
  - `admin_id` (nullable - null = global notification)
  - `title`, `message`, `type`, `priority`
  - `related_type`, `related_id`, `action_url`
  - `is_read`, `read_at`, `is_dismissed`
  - `icon`, `color`
- Indexed on:
  - `admin_id`, `is_read`, `created_at`
  - `type`, `created_at`
  - `priority`, `is_read`

## API Endpoints

### Client Endpoints (`/api/notifications`)
- `GET /` - List notifications (paginated)
- `GET /unread-count` - Get unread count
- `GET /recent` - Get recent 10 notifications
- `PUT /{id}/read` - Mark as read
- `PUT /read-all` - Mark all as read
- `DELETE /{id}` - Delete notification

### Admin Endpoints (`/api/admin/notifications`)
- `GET /` - List notifications (paginated)
- `GET /unread-count` - Get unread count
- `GET /recent` - Get recent 10 notifications
- `PUT /{id}/read` - Mark as read
- `PUT /read-all` - Mark all as read
- `PUT /{id}/dismiss` - Dismiss notification

## Notification Triggers

### Order Notifications
- **New Order**: Notifies admin and user
- **Status Update**: Notifies admin and user automatically
- **Tracking Number**: Included in shipped notifications

### Payment Notifications
- **Payment Verified**: Notifies user
- **Payment Rejected**: Notifies user

### Product Notifications
- **Product Sale**: Notifies all active users
- **New Featured Product**: Notifies all active users

## Data Flow

1. **Event Occurs** (e.g., order status change)
2. **Controller** calls `NotificationManager`
3. **NotificationManager** coordinates:
   - Creates admin notification via `AdminNotificationService`
   - Creates user notification via `UserNotificationService`
   - Uses database transactions for consistency
4. **Repository** handles database operations
5. **Transformer** formats response for API
6. **Controller** returns formatted response

## Error Handling

- All operations wrapped in try-catch blocks
- Database transactions ensure data integrity
- Comprehensive logging for debugging
- Graceful error responses to frontend

## Performance Optimizations

- Database indexes on frequently queried fields
- Chunking for bulk user notifications (100 users per chunk)
- Efficient queries with proper eager loading
- Caching considerations for future implementation

## Testing Recommendations

1. Test notification creation for both admin and user
2. Test database transactions rollback on errors
3. Test bulk notifications (100+ users)
4. Test notification filtering and pagination
5. Test read/unread status updates
6. Test notification deletion/dismissal
