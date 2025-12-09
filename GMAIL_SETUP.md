# Gmail Email Configuration

## Setup Instructions

### Step 1: Enable 2-Step Verification
1. Go to: https://myaccount.google.com/security
2. Enable **2-Step Verification** on your Google account

### Step 2: Generate App Password
1. Go to: https://myaccount.google.com/apppasswords
2. Select **"Mail"** from the dropdown
3. Select **"Other (Custom name)"** from device dropdown
4. Enter **"SkillBox"** as the name
5. Click **"Generate"**
6. **Copy the 16-character password** (you'll need this for .env)

### Step 3: Update .env File

Add or update these lines in your `.env` file:

```env
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-16-character-app-password
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME=SkillBox
MAIL_DEBUG=0
```

**Important Notes:**
- Use your **Gmail address** for `MAIL_USERNAME`
- Use the **16-character App Password** (not your regular Gmail password)
- `MAIL_FROM_ADDRESS` should match your Gmail address
- Set `MAIL_DEBUG=0` for production, `2` for debugging

### Step 4: Test

After updating `.env`, test the email sending:

1. **Run test script:**
   ```
   http://localhost/skillbox/public/test_mailtrap.php
   ```

2. **Or test via forgot password:**
   - Go to login page
   - Click "Forgot Password?"
   - Enter an email address
   - Check if email arrives in inbox

## Troubleshooting

### "Username and Password not accepted"
- Make sure you're using an **App Password**, not your regular Gmail password
- Verify 2-Step Verification is enabled
- Regenerate the App Password if needed

### "Connection timeout"
- Check if port 587 is blocked by firewall
- Verify internet connection
- Try using port 465 with SSL instead (requires code change)

### Emails not arriving
- Check spam folder
- Verify `MAIL_FROM_ADDRESS` matches your Gmail address
- Check Gmail account for any security alerts

## Security Notes

1. **Never commit `.env` file** to version control
2. **Use App Passwords** only (never use your regular Gmail password)
3. **Keep App Passwords secure** - treat them like regular passwords
4. **Revoke App Passwords** if compromised

