# Backend Implementation Prompt - Performance Staff API

## 📋 Task Overview

Implement backend API untuk **Staff Performance** feature yang menampilkan metrics kinerja staff/mekanik bengkel.

**Tech Stack:**
- Laravel (PHP)
- MySQL database
- RESTful API

---

## 🎯 Requirements Summary

Create API endpoints untuk:
1. Get performance metrics untuk ALL staff (aggregated)
2. Get detailed performance untuk INDIVIDUAL staff
3. Support time range filtering (today/week/month)
4. Calculate revenue, jobs completed, active jobs

---

## 📊 Database Schema Changes

### **1. Add Column to `services` Table**

```sql
-- Migration: add_performance_fields_to_services_table
ALTER TABLE services 
  ADD COLUMN assigned_to_user_id UUID NULL REFERENCES users(id),
  ADD COLUMN technician_name VARCHAR(255) NULL,
  ADD COLUMN completed_at TIMESTAMP NULL;

-- Add indexes for performance
CREATE INDEX idx_services_assigned_to ON services(assigned_to_user_id);
CREATE INDEX idx_services_status_assigned ON services(status, assigned_to_user_id);
CREATE INDEX idx_services_completed_at ON services(completed_at);
```

**Field Descriptions:**
- `assigned_to_user_id`: Foreign key ke users table (staff yang handle service ini)
- `technician_name`: Denormalized for quick access (optional optimization)
- `completed_at`: Timestamp kapan service completed (null jika belum)

### **2. Update Service Creation Logic**

When creating/updating service, automatically set:
- `assigned_to_user_id` = ID of assigned technician
- `completed_at` = current timestamp when status changed to "completed"

---

## 🔌 API Endpoints Specification

### **Endpoint 1: Get All Staff Performance**

```
GET /api/v1/owners/staff/performance
```

**Authorization:** Bearer Token (Role: owner)

**Query Parameters:**
```
workshop_uuid (required): UUID of the workshop
range (optional): "today" | "week" | "month" (default: "month")
month (optional): 1-12 (for specific month)
year (optional): 2025 (for specific year)
```

**Response Format:**
```json
{
  "success": true,
  "message": "Staff performance retrieved successfully",
  "data": [
    {
      "staff_id": "uuid-here",
      "staff_name": "Togar Siregar",
      "staff_email": "togar@example.com",
      "role": "mechanic",
      "specialist": "Diagnostik Mercedes & BMW",
      "photo_url": "https://...",
      "metrics": {
        "total_jobs_completed": 15,
        "total_revenue": 4500000,
        "active_jobs": 3,
        "completion_rate": 83.33,
        "average_revenue_per_job": 300000
      },
      "in_progress_jobs": [
        {
          "id": "uuid",
          "code": "WO-001-123456",
          "name": "Servis Ringan",
          "status": "in progress",
          "customer_name": "John Doe",
          "created_at": "2025-12-04T10:00:00Z"
        }
      ]
    }
  ],
  "meta": {
    "range": "month",
    "period": "December 2025",
    "total_staff": 5
  }
}
```

**Business Logic:**

```php
// Pseudo-code
function getStaffPerformance($workshopUuid, $range) {
    // 1. Get all staff/employees for this workshop
    $staff = Employee::where('workshop_uuid', $workshopUuid)
        ->with('user')
        ->get();
    
    // 2. Get date range
    $dateRange = calculateDateRange($range); // returns [start, end]
    
    // 3. For each staff, calculate metrics
    $performance = $staff->map(function($employee) use ($dateRange) {
        $userId = $employee->user_id;
        
        // Get services assigned to this user within date range
        $services = Service::where('assigned_to_user_id', $userId)
            ->whereBetween('created_at', $dateRange)
            ->get();
        
        // Calculate metrics
        $completed = $services->where('status', 'completed');
        $active = $services->whereIn('status', ['pending', 'in progress', 'accept']);
        
        $totalRevenue = $completed->sum(function($service) {
            $partsTotal = $service->items->sum('subtotal');
            return $service->price + $partsTotal;
        });
        
        return [
            'staff_id' => $userId,
            'staff_name' => $employee->user->name,
            'role' => $employee->role,
            'metrics' => [
                'total_jobs_completed' => $completed->count(),
                'total_revenue' => $totalRevenue,
                'active_jobs' => $active->count(),
            ],
            'in_progress_jobs' => $active->take(5)->values()
        ];
    });
    
    return $performance;
}
```

---

### **Endpoint 2: Get Individual Staff Performance**

```
GET /api/v1/owners/staff/{user_id}/performance
```

**Authorization:** Bearer Token (Role: owner)

**Path Parameters:**
```
user_id (required): UUID of the staff
```

**Query Parameters:**
```
workshop_uuid (required): UUID of the workshop
range (optional): "today" | "week" | "month" (default: "month")
```

**Response Format:**
```json
{
  "success": true,
  "message": "Staff performance retrieved successfully",
  "data": {
    "staff_id": "uuid-here",
    "staff_name": "Togar Siregar",
    "role": "mechanic",
    "metrics": {
      "total_jobs_completed": 15,
      "total_revenue": 4500000,
      "active_jobs": 3,
      "completion_rate": 83.33,
      "average_revenue_per_job": 300000,
      "fastest_job_completion": "2 hours",
      "average_completion_time": "4 hours"
    },
    "completed_jobs": [
      {
        "id": "uuid",
        "code": "WO-001-123456",
        "name": "Servis Ringan",
        "price": 300000,
        "customer_name": "John Doe",
        "completed_at": "2025-12-04T14:00:00Z",
        "completion_time": "3 hours"
      }
    ],
    "active_jobs": [...]
  }
}
```

---

## 📅 Date Range Calculation

```php
function calculateDateRange($range) {
    $now = Carbon::now();
    
    switch($range) {
        case 'today':
            return [
                $now->copy()->startOfDay(),
                $now->copy()->endOfDay()
            ];
            
        case 'week':
            return [
                $now->copy()->subDays(7)->startOfDay(),
                $now->copy()->endOfDay()
            ];
            
        case 'month':
            return [
                $now->copy()->startOfMonth(),
                $now->copy()->endOfMonth()
            ];
            
        default:
            return [
                $now->copy()->startOfMonth(),
                $now->copy()->endOfMonth()
            ];
    }
}
```

---

## 🔐 Authorization & Validation

### **Validation Rules:**

```php
// For GET /api/v1/owners/staff/performance
$rules = [
    'workshop_uuid' => 'required|uuid|exists:workshops,id',
    'range' => 'nullable|in:today,week,month',
    'month' => 'nullable|integer|min:1|max:12',
    'year' => 'nullable|integer|min:2020|max:2030',
];

// For GET /api/v1/owners/staff/{user_id}/performance
$rules = [
    'workshop_uuid' => 'required|uuid|exists:workshops,id',
    'range' => 'nullable|in:today,week,month',
];
```

### **Authorization:**

```php
// Verify that authenticated user is owner of the workshop
if (!$user->ownsWorkshop($workshopUuid)) {
    return response()->json([
        'success' => false,
        'message' => 'Unauthorized'
    ], 403);
}
```

---

## ⚡ Performance Optimization

### **1. Eager Loading**

```php
Service::with(['customer', 'vehicle', 'items'])
    ->where('assigned_to_user_id', $userId)
    ->get();
```

### **2. Query Optimization**

```php
// Use raw SQL for aggregation (faster)
DB::table('services')
    ->select(
        'assigned_to_user_id',
        DB::raw('COUNT(*) as total_jobs'),
        DB::raw('SUM(price) as total_revenue')
    )
    ->where('status', 'completed')
    ->whereBetween('created_at', $dateRange)
    ->groupBy('assigned_to_user_id')
    ->get();
```

### **3. Caching**

```php
// Cache performance data for 5 minutes
$cacheKey = "staff_performance_{$workshopUuid}_{$range}";

return Cache::remember($cacheKey, 300, function() use ($workshopUuid, $range) {
    return $this->calculatePerformance($workshopUuid, $range);
});
```

### **4. Pagination**

```php
// For large number of staff, paginate
$perPage = request('per_page', 20);
$performance = $performanceCollection->paginate($perPage);
```

---

## 🧪 Testing Requirements

### **Unit Tests:**

```php
// Test date range calculation
public function test_calculates_today_range_correctly()
public function test_calculates_week_range_correctly()
public function test_calculates_month_range_correctly()

// Test revenue calculation
public function test_calculates_revenue_with_parts()
public function test_calculates_revenue_without_parts()

// Test authorization
public function test_only_owner_can_access_performance()
public function test_cannot_access_other_workshop_performance()
```

### **Feature Tests:**

```php
public function test_can_get_all_staff_performance()
{
    $response = $this->getJson('/api/v1/owners/staff/performance?workshop_uuid=xxx');
    
    $response->assertOk();
    $response->assertJsonStructure([
        'success',
        'data' => [
            '*' => [
                'staff_id',
                'staff_name',
                'metrics' => [
                    'total_jobs_completed',
                    'total_revenue',
                    'active_jobs'
                ]
            ]
        ]
    ]);
}
```

---

## 📝 Implementation Checklist

### **Database:**
- [ ] Create migration for `services` table columns
- [ ] Add indexes for performance queries
- [ ] Update Service model with new fields
- [ ] Create seeder for test data (optional)

### **Backend Logic:**
- [ ] Create `StaffPerformanceController`
- [ ] Implement `index()` method (all staff)
- [ ] Implement `show()` method (individual staff)
- [ ] Create `StaffPerformanceService` for business logic
- [ ] Implement date range calculation helper
- [ ] Implement revenue calculation logic

### **API Routes:**
- [ ] Add routes to `api.php`
- [ ] Add middleware (auth, owner role check)
- [ ] Add route model binding for user

### **Validation:**
- [ ] Create `StaffPerformanceRequest` form request
- [ ] Add validation rules
- [ ] Add custom error messages

### **Optimization:**
- [ ] Add eager loading
- [ ] Implement caching layer
- [ ] Add database indexes
- [ ] Test query performance with large datasets

### **Testing:**
- [ ] Write unit tests for calculations
- [ ] Write feature tests for endpoints
- [ ] Test with different time ranges
- [ ] Test authorization rules

### **Documentation:**
- [ ] Add API documentation (Postman/OpenAPI)
- [ ] Document response formats
- [ ] Add usage examples

---

## 🚀 Deployment Steps

1. **Run Migration:**
```bash
php artisan migrate
```

2. **Clear Cache:**
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

3. **Test Endpoints:**
```bash
php artisan test --filter StaffPerformanceTest
```

4. **Update Postman Collection** (if applicable)

5. **Update API Documentation**

---

## 📞 API Testing Examples

### **cURL Example:**

```bash
# Get all staff performance
curl -X GET "https://api.example.com/api/v1/owners/staff/performance?workshop_uuid=xxx&range=month" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"

# Get individual staff performance
curl -X GET "https://api.example.com/api/v1/owners/staff/USER_UUID/performance?workshop_uuid=xxx&range=week" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

---

## ⚠️ Important Notes

1. **Data Integrity:**
   - Ensure `assigned_to_user_id` is set for all new services
   - Migration existing services (assign to default user or null)

2. **Performance:**
   - Monitor query performance with EXPLAIN
   - Add pagination for workshops dengan banyak staff
   - Consider materialized views for very large datasets

3. **Security:**
   - Always verify workshop ownership
   - Validate all input parameters
   - Rate limit API calls if needed

4. **Backwards Compatibility:**
   - `assigned_to_user_id` is nullable untuk existing services
   - Frontend can handle null/empty data gracefully

---

## 🎯 Success Criteria

✅ API returns correct performance data for all staff
✅ Date range filtering works accurately
✅ Revenue calculation includes service price + parts
✅ Authorization prevents unauthorized access
✅ Query performance < 500ms for 100 staff
✅ All tests passing
✅ API documentation updated

---

Ready to implement! 🚀
