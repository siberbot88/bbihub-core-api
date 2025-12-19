# Test 3: Password Policy Validation - Step by Step

**Objective**: Validate password complexity requirements working correctly

---

## 🧪 Test Procedure (Copy-paste commands below)

### Step 1: Request OTP
```bash
curl -X POST http://localhost:8000/api/v1/auth/forgot-password \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"admin@bbi.com"}'
```

**Expected**: OTP sent to email (or check database)

**Check OTP in database**:
```sql
-- Open SQLite database
SELECT email, token, created_at FROM password_reset_tokens WHERE email = 'admin@bbi.com';
```

---

### Step 2: Test WEAK Password (Should FAIL)

```bash
curl -X POST http://localhost:8000/api/v1/auth/reset-password \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email":"admin@bbi.com",
    "otp":"REPLACE_WITH_ACTUAL_OTP",
    "password":"weak",
    "password_confirmation":"weak"
  }'
```

**Expected Result**:
```json
{
  "success": false,
  "message": "Validasi gagal.",
  "errors": {
    "password": [
      "The password field must be at least 8 characters.",
      "The password field must contain at least one uppercase and one lowercase letter.",
      "The password field must contain at least one symbol.",
      "The password field must contain at least one number."
    ]
  }
}
```

---

### Step 3: Test Password Too Short (Should FAIL)

```bash
curl -X POST http://localhost:8000/api/v1/auth/reset-password \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email":"admin@bbi.com",
    "otp":"REPLACE_WITH_ACTUAL_OTP",
    "password":"Test1!",
    "password_confirmation":"Test1!"
  }'
```

**Expected**: Error "must be at least 8 characters"

---

### Step 4: Test Password No Symbol (Should FAIL)

```bash
curl -X POST http://localhost:8000/api/v1/auth/reset-password \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email":"admin@bbi.com",
    "otp":"REPLACE_WITH_ACTUAL_OTP",
    "password":"Test1234",
    "password_confirmation":"Test1234"
  }'
```

**Expected**: Error "must contain at least one symbol"

---

### Step 5: Test STRONG Password (Should PASS)

```bash
curl -X POST http://localhost:8000/api/v1/auth/reset-password \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email":"admin@bbi.com",
    "otp":"REPLACE_WITH_ACTUAL_OTP",
    "password":"Test@12345!",
    "password_confirmation":"Test@12345!"
  }'
```

**Expected Result**:
```json
{
  "success": true,
  "message": "Password berhasil diubah! Silakan login dengan password baru."
}
```

---

## 📝 Quick Test Script (One-liner per test)

### Alternative: Test with existing user WITHOUT OTP

If you want to test just password validation without OTP flow:

```bash
# Test 1: Weak password (less than 8 chars, no complexity)
curl -X POST http://localhost:8000/api/v1/auth/reset-password -H "Content-Type: application/json" -d '{"email":"admin@bbi.com","otp":"000000","password":"weak","password_confirmation":"weak"}'

# Test 2: No uppercase
curl -X POST http://localhost:8000/api/v1/auth/reset-password -H "Content-Type: application/json" -d '{"email":"admin@bbi.com","otp":"000000","password":"test@123!","password_confirmation":"test@123!"}'

# Test 3: No symbol  
curl -X POST http://localhost:8000/api/v1/auth/reset-password -H "Content-Type: application/json" -d '{"email":"admin@bbi.com","otp":"000000","password":"Test1234","password_confirmation":"Test1234"}'

# Test 4: Strong password (for reference - will fail on OTP but shows password validation passed)
curl -X POST http://localhost:8000/api/v1/auth/reset-password -H "Content-Type: application/json" -d '{"email":"admin@bbi.com","otp":"000000","password":"Test@123!","password_confirmation":"Test@123!"}'
```

**Note**: These will fail on OTP validation but you can see if password validation happens FIRST (in errors array)

---

## ✅ Success Criteria

**Test PASSES if**:
- ❌ "weak" password rejected with clear error messages
- ❌ "Test1!" rejected (too short)
- ❌ "Test1234" rejected (no symbol)
- ✅ "Test@12345!" accepted (all requirements met)

---

## 🎯 Expected Validation Order

Laravel validates in this order:
1. Required fields (email, otp, password)
2. **Password complexity** (our new rules)
3. OTP correctness
4. OTP expiration

So even with wrong OTP, you'll see password errors first! ✅
