# Week 1 Security Fixes - Implementation Log

**Date**: 18 Desember 2025  
**Status**: ✅ COMPLETED (3/4 tasks)

---

## ✅ Task 1: OTP Expiration Validation

**File Modified**: `app/Http/Controllers/Api/ForgotPasswordController.php`  
**Lines**: 122-136

### Changes:
- Added 15-minute expiration check using `Carbon::parse($record->created_at)->addMinutes(15)->isPast()`
- Expired OTPs automatically deleted from database
- Clear error message: "Kode OTP telah kadaluarsa. Silakan minta kode baru."

### Code:
```php
// Check OTP expiration (15 minutes)
$createdAt = Carbon::parse($record->created_at);
if ($createdAt->addMinutes(15)->isPast()) {
    DB::table('password_reset_tokens')->where('email', $request->email)->delete();
    return response()->json([
        'success' => false,
        'message' => 'Kode OTP telah kadaluarsa. Silakan minta kode baru.'
    ], 400);
}
```

### Testing:
```bash
# Manual test: Update created_at to 20 minutes ago in database
# Then try reset password - should get "OTP telah kadaluarsa" error
```

---

## ✅ Task 2: Rate Limiting

**File Modified**: `routes/api.php`  
**Lines**: 27-36

### Changes:
- Added `throttle` middleware to 3 endpoints:
  - `forgot-password`: 3 requests per 10 minutes
  - `verify-otp`: 5 attempts per minute
  - `reset-password`: 5 attempts per 10 minutes

### Code:
```php
Route::post('forgot-password', [ForgotPasswordController::class, 'sendOtp'])
    ->middleware('throttle:3,10'); // Max 3 requests per 10 minutes

Route::post('verify-otp', [ForgotPasswordController::class, 'verifyOtp'])
    ->middleware('throttle:5,1'); // Max 5 attempts per minute

Route::post('reset-password', [ForgotPasswordController::class, 'resetPassword'])
    ->middleware('throttle:5,10'); // Max 5 attempts per 10 minutes
```

### Testing:
```bash
# Test by making 4 requests in 10 minutes
for i in {1..4}; do
  curl -X POST http://localhost:8000/api/v1/auth/forgot-password \
    -H "Content-Type: application/json" \
    -d '{"email":"test@example.com"}'
done
# Expected: 4th request returns 429 Too Many Requests
```

---

## ✅ Task 3: Password Policy Enforcement

**File Modified**: `app/Http/Controllers/Api/ForgotPasswordController.php`  
**Lines**: 105-118

### Changes:
- Upgraded from `min:6` to `Password::min(8)` with complexity requirements
- Requires:
  - Minimum 8 characters
  - Mixed case (uppercase + lowercase)
  - At least one number
  - At least one special character
  - Check against compromised passwords (haveibeenpwned.com database)

### Code:
```php
'password' => [
    'required',
    'confirmed',
    \Illuminate\Validation\Rules\Password::min(8)
        ->mixedCase()
        ->numbers()
        ->symbols()
        ->uncompromised()
],
```

### Testing:
```bash
# Test weak password (should FAIL)
curl -X POST http://localhost:8000/api/v1/auth/reset-password \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","otp":"123456","password":"123456","password_confirmation":"123456"}'
# Expected: Validation error

# Test strong password (should PASS)
curl -X POST http://localhost:8000/api/v1/auth/reset-password \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","otp":"123456","password":"Test@123!","password_confirmation":"Test@123!"}'
# Expected: Success
```

---

## ⏸️ Task 4: IDOR Prevention (Deferred)

**Reason**: Memerlukan investigasi lebih lanjut untuk memahami struktur ServiceApiController dan ownership validation yang sudah ada.

**Next Steps**:
1. Review `app/Http/Controllers/Api/ServiceApiController.php`
2. Check apakah sudah ada ownership validation
3. Jika belum, tambahkan validation untuk:
   - `show()` method
   - `update()` method
   - `destroy()` method

---

## Summary

### ✅ Completed (3/4 tasks in Week 1)
- OTP expiration validation
- Rate limiting for auth endpoints
- Password policy enforcement

### Impact
| Security Issue | Before | After | Risk Reduction |
|---------------|--------|-------|----------------|
| OTP brute-force | Unlimited time | 15 min expiry | **HIGH** → LOW |
| Endpoint spam | No limit | Throttled | **HIGH** → LOW |
| Weak passwords | Min 6 char | Min 8 + complex | **MEDIUM** → LOW |

### Files Modified
1. `app/Http/Controllers/Api/ForgotPasswordController.php` (2 changes)
2. `routes/api.php` (1 change)

### Total Lines Changed
- +25 lines added
- ~3 lines modified
- **Net: +28 lines of code**

---

## Verification Checklist

- [ ] Test OTP expiration manually (update `created_at` in DB)
- [ ] Test rate limiting (4+ requests in 10 minutes)
- [ ] Test password policy:
  - [ ] "123456" rejected ✓
  - [ ] "password" rejected ✓
  - [ ] "Test@123!" accepted ✓
- [ ] Mobile app compatible dengan perubahan (error messages)
- [ ] API documentation updated

---

## Next Steps

1. **Complete Task 4** - IDOR Prevention (estimate: 2 days)
2. **Testing** - Run all test cases (TC-001 to TC-003)
3. **Documentation** - Update API docs dengan password requirements
4. **Commit** - Create commit dengan clear message
5. **Week 2** - Start input validation standardization

---

**Status**: 🟢 On track for Week 1 completion
