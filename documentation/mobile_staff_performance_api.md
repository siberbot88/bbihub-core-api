# Mobile Staff Performance API Integration Guide

This document provides the specification for integrating the Staff Performance features into the Mobile Application.

## Base URL
`{{BASE_URL}}/api/v1`

## Authentication
All endpoints require a valid Bearer Token.
Header: `Authorization: Bearer <token>`

---

## 1. Get All Staff Performance
Retrieves a list of all staff members in a workshop with their aggregated performance metrics.

**Endpoint:**
`GET /owners/staff/performance`

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `workshop_uuid` | UUID | Yes | The UUID of the workshop. |
| `range` | String | No | Date range filter. Options: `today`, `week`, `month`. Default: `month`. |
| `month` | Integer | No | Filter by specific month (1-12). Requires `range=month`. |
| `year` | Integer | No | Filter by specific year (e.g., 2025). Requires `range=month`. |

**Example Request:**
```http
GET /api/v1/owners/staff/performance?workshop_uuid=123e4567-e89b-12d3-a456-426614174000&range=month
Authorization: Bearer <token>
```

**Success Response (200 OK):**
```json
{
    "success": true,
    "message": "Staff performance retrieved successfully",
    "data": [
        {
            "staff_id": "9d8e7f6a-5b4c-3d2e-1f0a-9b8c7d6e5f4a",
            "staff_name": "Budi Santoso",
            "role": "Mechanic",
            "photo_url": "https://example.com/storage/photos/budi.jpg",
            "metrics": {
                "total_jobs_completed": 15,
                "total_revenue": 4500000,
                "active_jobs": 2,
                "average_revenue_per_job": 300000
            }
        },
        {
            "staff_id": "1a2b3c4d-5e6f-7g8h-9i0j-1k2l3m4n5o6p",
            "staff_name": "Siti Aminah",
            "role": "Service Advisor",
            "photo_url": null,
            "metrics": {
                "total_jobs_completed": 42,
                "total_revenue": 12500000,
                "active_jobs": 5,
                "average_revenue_per_job": 297619.05
            }
        }
    ],
    "meta": {
        "range": "month",
        "period": "December 2025",
        "total_staff": 2
    }
}
```

---

## 2. Get Individual Staff Performance
Retrieves detailed performance metrics for a specific staff member, including a list of recently completed jobs.

**Endpoint:**
`GET /owners/staff/{user_id}/performance`

**Path Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `user_id` | UUID | Yes | The UUID of the staff member (User ID). |

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `workshop_uuid` | UUID | Yes | The UUID of the workshop. |
| `range` | String | No | Date range filter. Options: `today`, `week`, `month`. Default: `month`. |

**Example Request:**
```http
GET /api/v1/owners/staff/9d8e7f6a-5b4c-3d2e-1f0a-9b8c7d6e5f4a/performance?workshop_uuid=123e4567-e89b-12d3-a456-426614174000&range=week
Authorization: Bearer <token>
```

**Success Response (200 OK):**
```json
{
    "success": true,
    "message": "Staff performance retrieved successfully",
    "data": {
        "staff_id": "9d8e7f6a-5b4c-3d2e-1f0a-9b8c7d6e5f4a",
        "staff_name": "Budi Santoso",
        "role": "Mechanic",
        "photo_url": "https://example.com/storage/photos/budi.jpg",
        "metrics": {
            "total_jobs_completed": 5,
            "total_revenue": 1500000,
            "active_jobs": 2,
            "average_revenue_per_job": 300000,
            "completed_jobs": [
                {
                    "id": 101,
                    "code": "WO-20251204-001",
                    "name": "Ganti Oli & Tune Up",
                    "price": 350000,
                    "completed_at": "2025-12-04T10:30:00.000000Z"
                },
                {
                    "id": 98,
                    "code": "WO-20251203-015",
                    "name": "Service Rem",
                    "price": 250000,
                    "completed_at": "2025-12-03T14:15:00.000000Z"
                }
            ]
        }
    }
}
```

---

## Error Responses

**401 Unauthorized:**
Token is missing or invalid.

**403 Forbidden:**
User is not the owner of the specified workshop.
```json
{
    "message": "Unauthorized access to this workshop"
}
```

**404 Not Found:**
Staff member not found or does not belong to the workshop.
```json
{
    "message": "Staff not found or not in this workshop"
}
```

**422 Unprocessable Entity:**
Validation error (e.g., missing `workshop_uuid`).
```json
{
    "message": "The workshop uuid field is required.",
    "errors": {
        "workshop_uuid": [
            "The workshop uuid field is required."
        ]
    }
}
```
