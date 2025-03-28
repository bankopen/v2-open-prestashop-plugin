# PrestaShop Payment Module Installation Guide

This guide provides step-by-step instructions to install a payment module on your PrestaShop store and clear the cache to ensure proper functionality before configuring the module.

---

## Step 1: Install the Payment Module

1. **Download the Module:**
    - Obtain the payment module files (usually in `.zip` format) from the provider or developer.

2. **Access Your PrestaShop Admin Panel:**
    - Log in to your PrestaShop back office.

3. **Upload the Module:**
    - Go to **Modules > Module Manager**.
    - Click on **Upload a module** (button at the top right).
    - Drag and drop the `.zip` file or click to select it from your computer.
    - Wait for the module to upload and install.

4. **Confirm Installation:**
    - Once installed, you should see a confirmation message.
    - The module will now appear in your **Modules > Module Manager** list.

---

## Step 2: Clear PrestaShop Cache

After installing the module, it is essential to clear the cache to ensure the module works correctly.

1. **Go to the Advanced Parameters Menu:**
    - In your PrestaShop admin panel, navigate to **Advanced Parameters > Performance**.

2. **Clear Cache:**
    - Under the **Cache** section, click the **Clear Cache** button.
    - Ensure the option **"Clear cache"** is selected and confirm.

3. **Disable Cache (Optional):**
    - If you are still in the development or testing phase, you can disable the cache by selecting **"No"** under **"Enable cache"**.
    - Click **Save** to apply the changes.

---

## Step 3: Configure the Payment Module

1. **Locate the Module:**
    - Go back to **Modules > Module Manager**.
    - Find the installed payment module in the list.

2. **Configure the Module:**
    - Click the **Configure** button next to the module.
    - Fill in the required settings (e.g., API keys, payment methods, etc.) as per the module's documentation.
    - Save your changes.

3. **Test the Payment Process:**
    - Place a test order on your store to ensure the payment module is working correctly.

---

## Troubleshooting

- If the module does not appear or function as expected after installation, ensure the cache is cleared and try again.
- If issues persist, contact the module developer or your hosting provider for further assistance.

---

**Note:** Always back up your store before installing or updating modules to avoid data loss.

For further assistance, refer to the module's official documentation or contact the module provider.
