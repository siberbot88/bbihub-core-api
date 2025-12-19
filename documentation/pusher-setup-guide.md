# Pusher Cloud Setup Guide - Live Chat Feature

## 🎯 Quick Setup (15 minutes)

### Step 1: Create Pusher Account (5 min)

1. **Buka** https://pusher.com/
2. **Sign Up** (klik "Get started free")
3. **Verify email** Anda

### Step 2: Create Pusher Channel App (3 min)

1. **Login** ke Pusher Dashboard
2. Klik **"Create app"**
3. **Isi form**:
   - **App name**: `BBIHUB-LiveChat`
   - **Cluster**: Pilih **`ap-southeast-1` (Singapore)** atau **`ap1` (Asia Pacific)**
   - **Tech stack**: Pilih **Laravel** untuk frontend & backend
4. Klik **"Create app"**

### Step 3: Get Credentials (2 min)

Setelah app dibuat, Anda akan dapat credentials:

```
app_id: XXXXXX
key: XXXXXXXXXXXXXXXX
secret: XXXXXXXXXXXXXX
cluster: ap1
```

**Copy semua credentials ini** untuk Step 4!

### Step 4: Configure Laravel .env (5 min)

**Buka file** `e:\BBIHUB\backend\.env`

**Tambahkan/Update** baris berikut:

```env
# Pusher Configuration
BROADCAST_DRIVER=pusher

PUSHER_APP_ID=YOUR_APP_ID_HERE
PUSHER_APP_KEY=YOUR_KEY_HERE
PUSHER_APP_SECRET=YOUR_SECRET_HERE
PUSHER_APP_CLUSTER=ap1

# Vite Frontend Configuration
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
VITE_APP_NAME="${APP_NAME}"
```

**Replace**:
- `YOUR_APP_ID_HERE` dengan app_id dari Pusher
- `YOUR_KEY_HERE` dengan key dari Pusher
- `YOUR_SECRET_HERE` dengan secret dari Pusher
- `ap1` dengan cluster yang Anda pilih (jika berbeda)

### Step 5: Install Pusher PHP SDK (1 min)

```bash
composer require pusher/pusher-php-server
```

### Step 6: Restart Dev Server (1 min)

```bash
# Stop composer run dev (Ctrl+C)
composer run dev
```

---

## ✅ Verification

Setelah setup, test connection:

### Test 1: Check Config

```bash
php artisan tinker
```

```php
config('broadcasting.connections.pusher')
// Should show your Pusher credentials

exit
```

### Test 2: Test Broadcasting

```bash
php artisan make:event TestEvent
```

Edit `app/Events/TestEvent.php`:
```php
class TestEvent implements ShouldBroadcast
{
    public function broadcastOn()
    {
        return new Channel('test-channel');
    }
}
```

Fire event:
```bash
php artisan tinker
```

```php
event(new App\Events\TestEvent());
// Check Pusher Dashboard -> Debug Console for event
```

---

## 📊 Free Tier Limits

**Pusher Free Tier includes**:
- ✅ 200,000 messages/day
- ✅ 100 concurrent connections
- ✅ Unlimited channels
- ✅ SSL included

**For BBI Hub**: Free tier is **MORE than enough** untuk testing & small production!

---

## 🔧 Troubleshooting

### Issue: "Connection refused"
**Fix**: Check internet connection & Pusher credentials

### Issue: "Invalid key"
**Fix**: Pastikan `VITE_PUSHER_APP_KEY` match dengan `PUSHER_APP_KEY`

### Issue: Events not showing in Pusher Debug Console
**Fix**: 
1. Check `BROADCAST_DRIVER=pusher` di `.env`
2. Restart `composer run dev`
3. Clear browser cache

---

## 🎯 Next Steps

After Pusher configured:
1. ✅ Create chat migrations & models
2. ✅ Build chat API endpoints
3. ✅ Create chat UI components
4. ✅ Test real-time messaging

**Estimated time to working chat**: 1-2 days after Pusher setup!

---

## 💡 Pro Tips

1. **Debugging**: Enable Pusher Debug Console di dashboard untuk lihat events real-time
2. **Development**: Use free tier, upgrade saat production scale
3. **Migration**: Bisa migrate ke Soketi nanti tanpa ubah frontend code (Pusher-compatible)

---

**Ready to start?** 
1. Get Pusher credentials
2. Update `.env`
3. Install `pusher/pusher-php-server`
4. Test connection
5. Lanjut ke Phase 2!
