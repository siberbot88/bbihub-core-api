# Week 1 Security Fixes - Testing Guide

**Date**: 18 Desember 2025  
**Status**: Ready for Testing

---

## Testing Status: ⚠️ MANUAL TESTING REQUIRED

**Current State**: 
- ❌ No automated tests yet (tests folder exists but empty)
- ✅ Implementation complete
- ⏳ Need manual testing verification

---

## 🧪 Manual Testing Procedures

### Test 1: OTP Expiration Validation

**Objective**: Verify OTP expires after 15 minutes

**Steps**:
1. Request OTP via forgot-password endpoint
```bash
curl -X POST http://localhost:8000/api/v1/auth/forgot-password \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com"}'
```

2. Check email for OTP (or check database)
```sql
SELECT * FROM password_reset_tokens WHERE email = 'test@example.com';
```

3. **Wait 16 minutes** OR manually update `created_at` in database:
```sql
UPDATE password_reset_tokens 
SET created_at = datetime('now', '-20 minutes') 
WHERE email = 'test@example.com';
```

4. Try to reset password with that OTP:
```bash
curl -X POST http://localhost:8000/api/v1/auth/reset-password \
  -H "Content-Type: application/json" \
  -d '{
    "email":"test@example.com",
    "otp":"123456",
    "password":"Test@123!",
    "password_confirmation":"Test@123!"
  }'
```

**Expected Result**:
```json
{
  "success": false,
  "message": "Kode OTP telah kadaluarsa. Silakan minta kode baru."
}
```

**Pass Criteria**: ✅ OTP rejected, expired OTP deleted from DB

---

### Test 2: Rate Limiting

**Objective**: Verify rate limits prevent brute-force

#### 2A: Forgot Password Rate Limit (3 requests / 10 minutes)

```bash
# Send 4 requests rapidly
for i in {1..4}; do
  echo "=== Request $i ==="
  curl -X POST http://localhost:8000/api/v1/auth/forgot-password \
    -H "Content-Type: application/json" \
    -d '{"email":"test@example.com"}'
  echo -e "\n"
done
```

**Expected Result**:
- Requests 1-3: Success (200 OK)
- Request 4: **429 Too Many Requests**

Response for 4th request:
```json
{
  "message": "Too Many Attempts.",
  "exception": "Illuminate\\Http\\Exceptions\\ThrottleRequestsException"
}
```

**Pass Criteria**: ✅ 4th request blocked with 429 status

#### 2B: Verify-OTP Rate Limit (5 requests / 1 minute)

```bash
for i in {1..6}; do
  echo "=== Request $i ==="
  curl -X POST http://localhost:8000/api/v1/auth/verify-otp \
    -H "Content-Type: application/json" \
    -d '{"email":"test@example.com","otp":"123456"}'
  echo -e "\n"
done
```

**Expected Result**: Request 6 → 429 Too Many Requests

---

### Test 3: Password Policy Enforcement

**Objective**: Verify strong password requirements

#### 3A: Test Weak Passwords (Should FAIL)

```bash
# Test 1: Too short (less than 8 characters)
curl -X POST http://localhost:8000/api/v1/auth/reset-password \
  -H "Content-Type: application/json" \
  -d '{
    "email":"test@example.com",
    "otp":"123456",
    "password":"Test1!",
    "password_confirmation":"Test1!"
  }'
```

**Expected**: 422 Validation Error
```json
{
  "success": false,
  "message": "Validasi gagal.",
  "errors": {
    "password": ["The password field must be at least 8 characters."]
  }
}
```

#### 3B: Test Missing Requirements

```bash
# No uppercase
curl -X POST ... -d '{"password":"test@123!","password_confirmation":"test@123!"}'
# Expected error: "must contain at least one uppercase"

# No lowercase
curl -X POST ... -d '{"password":"TEST@123!","password_confirmation":"TEST@123!"}'
# Expected error: "must contain at least one lowercase"

# No numbers
curl -X POST ... -d '{"password":"Test@Pass!","password_confirmation":"Test@Pass!"}'
# Expected error: "must contain at least one number"

# No symbols
curl -X POST ... -d '{"password":"Test1234","password_confirmation":"Test1234"}'
# Expected error: "must contain at least one symbol"
```

#### 3C: Test Strong Password (Should PASS)

```bash
curl -X POST http://localhost:8000/api/v1/auth/reset-password \
  -H "Content-Type: application/json" \
  -d '{
    "email":"test@example.com",
    "otp":"123456",
    "password":"Test@12345!",
    "password_confirmation":"Test@12345!"
  }'
```

**Expected**: 200 Success
```json
{
  "success": true,
  "message": "Password berhasil diubah! Silakan login dengan password baru."
}
```

**Pass Criteria**: ✅ All weak passwords rejected, strong password accepted

---

### Test 4: Password Requirements Checklist (Mobile)

**Objective**: Verify realtime validation UI in Flutter app

**Steps**:
1. Open Flutter app
2. Navigate to Reset Password page
3. Start typing password in "New Password" field

**Expected Behavior**:
- Checklist appears below password field
- As you type, requirements update in realtime:
  - ❌ Red X when requirement NOT met
  - ✅ Green checkmark when requirement met
- All 5 requirements shown:
  - Minimal 8 karakter
  - Huruf besar (A-Z)
  - Huruf kecil (a-z)
  - Angka (0-9)
  - Simbol (!@#$%^&*)

**Test Cases**:
- Type "test" → All red X except lowercase ✅
- Type "Test" → lowercase ✅, uppercase ✅, others ❌
- Type "Test123" → min 8 ✅, lower ✅, upper ✅, number ✅, symbol ❌
- Type "Test@123!" → ALL GREEN ✅✅✅✅✅

**Pass Criteria**: ✅ Checklist updates in realtime, accurate validation

---

### Test 5: IDOR Prevention (Already Implemented)

**Objective**: Verify ownership validation prevents cross-owner access

**Setup**:
1. Create 2 owners with different workshops
2. Each owner has services in their workshop

**Steps**:

```bash
# 1. Login as Owner A
TOKEN_A=$(curl -s -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"ownerA@example.com","password":"password"}' \
  | jq -r '.data.access_token')

# 2. Get Owner A's service ID
SERVICE_A=$(curl -s -X GET http://localhost:8000/api/v1/owners/services \
  -H "Authorization: Bearer $TOKEN_A" \
  | jq -r '.data[0].id')

echo "Owner A Service ID: $SERVICE_A"

# 3. Login as Owner B
TOKEN_B=$(curl -s -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"ownerB@example.com","password":"password"}' \
  | jq -r '.data.access_token')

# 4. Try to access Owner A's service using Owner B's token (Should FAIL)
curl -X GET "http://localhost:8000/api/v1/owners/services/$SERVICE_A" \
  -H "Authorization: Bearer $TOKEN_B"
```

**Expected Result**:
```json
{
  "message": "This action is unauthorized."
}
```
**Status Code**: 403 Forbidden

**Pass Criteria**: ✅ Owner B cannot view Owner A's service

---

## 📊 Testing Checklist

### Backend Tests
- [ ] OTP expiration after 15 minutes
- [ ] OTP auto-deleted when expired
- [ ] Rate limiting: forgot-password (3/10min)
- [ ] Rate limiting: verify-otp (5/1min)  
- [ ] Rate limiting: reset-password (5/10min)
- [ ] Password validation: min 8 characters
- [ ] Password validation: mixed case required
- [ ] Password validation: numbers required
- [ ] Password validation: symbols required
- [ ] Password validation: uncompromised check
- [ ] IDOR prevention: ServiceApiController
- [ ] IDOR prevention: cross-workshop access denied

### Mobile Tests
- [ ] Password requirements checklist visible
- [ ] Checklist updates realtime as user types
- [ ] All 5 requirements shown correctly
- [ ] Visual feedback (green ✅ vs red ❌)
- [ ] Submit with weak password → see backend error
- [ ] Submit with strong password → success

### Integration Tests
- [ ] Full reset password flow (request OTP → verify → reset → login)
- [ ] Rate limit triggers → wait → retry success
- [ ] Expired OTP → request new OTP → success

---

## 🤖 Automated Testing (Recommended for Future)

### Laravel Feature Tests to Create

```php
// tests/Feature/Auth/PasswordResetTest.php

public function test_otp_expires_after_15_minutes()
{
    // Create user
    // Request OTP
    // Manually set created_at to 20 minutes ago
    // Try reset password
    // Assert error "OTP expired"
}

public function test_forgot_password_rate_limit()
{
    // Send 4 requests rapidly
    // Assert first 3 success
    // Assert 4th request 429
}

public function test_password_must_be_strong()
{
    // Request OTP
    // Try reset with weak password "123456"
    // Assert validation error
    
    // Try reset with strong password "Test@123!"
    // Assert success
}
```

### How to Run (When tests exist):
```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/Auth/PasswordResetTest.php

# Run with coverage
php artisan test --coverage
```

---

## ✅ Test Results Summary

| Test | Status | Pass/Fail | Notes |
|------|--------|-----------|-------|
| OTP Expiration | ⏳ Pending | - | Need manual verification |
| Rate Limiting | ⏳ Pending | - | Test with curl commands |
| Password Policy | ⏳ Pending | - | Test weak vs strong passwords |
| Mobile Checklist | ⏳ Pending | - | Test in Flutter app |
| IDOR Prevention | ✅ Implemented | PASS | Already using Laravel Policy |

**Overall Status**: 📝 **Ready for Manual Testing**

---

## 🚀 Next Steps

1. **Manual Testing** (Hari ini/besok):
   - Follow test procedures di atas
   - Document hasil di security-fixes-log.md

2. **Automated Tests** (Week 2):
   - Create Laravel Feature Tests
   - Add to CI/CD pipeline

3. **After Testing**:
   - Update task.md dengan test results
   - Mark Week 1 as COMPLETE ✅
   - Move to Week 2 tasks

---

**Testing By**: [Tester Name]  
**Date**: [Test Date]  
**Result**: PASS / FAIL / PARTIAL  
**Notes**: [Add any issues found]
