# Admin Dashboard - Quick Start Guide

## 🚀 Getting Started

### Accessing the Dashboard

1. **Login to Admin Panel**
   - Go to: `http://localhost/adidev/admin/`
   - Enter admin credentials
   - You'll be redirected to dashboard

2. **Navigate Using Sidebar**
   - Dashboard link at top of sidebar menu
   - Direct navigation: `admin/dashboard.php`

---

## 📊 Dashboard Page Features

### What You'll See

#### 1. **Top Statistics Cards** (4 Cards)
```
Total Orders        | Pending Orders   | Delivered Orders   | Total Revenue
42 orders          | 8 pending        | 15 delivered       | ₹2,45,630
All time orders    | Need attention   | Completed orders   | Completed sales
```

**Click Action**: Each card is clickable to filter orders by that category

#### 2. **Revenue Chart** (7-Day Overview)
- Shows revenue for last 7 days
- Hover over bars to see exact amount
- Useful for: Revenue trend analysis

#### 3. **Order Status Distribution** (Pie-Chart Style)
- Shows count for each status
- Displays percentage
- Color-coded status indicators
- Useful for: Order status overview

#### 4. **Recent Orders Table** (10 Most Recent)
- Order ID (clickable)
- Customer Name & Email
- Total Amount
- Order Status (color badge)
- Payment Status (color badge)
- Date
- Quick Actions: [View] [Edit]

**Click Action**: 
- Order ID → View full order details
- [View] → Open order details page
- [Edit] → Edit order information

---

## 📋 Orders Management Page

### Accessing Orders List

**Path**: `admin/orders.php`
**Sidebar**: Orders → All Orders

### Searching & Filtering

#### Search Box
```
Enter any of:
- Order ID (e.g., "ORD-2026-001")
- Customer Name (e.g., "Rajesh")
- Customer Email (e.g., "test@example.com")
- Press Enter to search
```

#### Status Filter Dropdown
```
All Status (default)
├─ Pending
├─ Payment Received
├─ Processing
├─ Shipped
├─ Delivered
└─ Cancelled
```

#### Payment Status Filter
```
All Payment Status (default)
├─ Paid
├─ Pending
└─ Failed
```

#### Export Button
- Prepare orders data for download
- (Currently shows alert - to be implemented)

### Table Actions

**For Each Order Row:**
- Click Order ID → View Details
- Click [View] Button → View Details
- Click [Edit] Button → Edit Order

### Pagination

- Shows 20 orders per page
- Navigation: [Previous] [1] [2] [3] [Next]
- Click page number to jump

---

## 👁️ Order Details Page

### How to Access

1. From Dashboard → Click order ID or [View]
2. From Orders List → Click order ID or [View]
3. Direct URL: `admin/order-details.php?id=1`

### Information Sections

#### Left Column

**Order Information**
- Order Number (e.g., ORD-2026-001)
- Order Date & Time
- Order Status (with color badge)
- Order Type (Regular/Bulk)

**Customer Information**
- Full Name
- Email Address (clickable)
- Phone Number (clickable)
- GST Number (if available)

**Shipping Address**
- Full formatted address
- Copy-friendly format

**Billing Address**
- Full formatted address
- Usually same as shipping

**Order Items Table**
- Product Name & Code
- Quantity
- Unit Price
- Total Price

#### Right Column

**Order Summary**
- Subtotal amount
- Discount (if any)
- Tax amount
- Shipping charges
- **Grand Total** (highlighted)
- Amount Paid
- Amount Due

**Payment Information**
- Payment Method (COD, Card, UPI, etc.)
- Payment Status (color badge)
- Transaction ID (if paid)
- Paid Date & Time

**Action Buttons**
- [Edit Order] → Modify order details
- [Print] → Browser print dialog

**Order Timeline**
- Visual timeline of order progress
- Shows completed milestones:
  - ✓ Order Created (always shown)
  - ✓ Order Confirmed (if status reached)
  - ✓ Order Shipped (if status reached)
  - ✓ Order Delivered (if status reached)

---

## ✏️ Edit Order Page

### How to Access

1. From Order Details → Click [Edit Order]
2. Direct URL: `admin/order-edit.php?id=1`

### Editable Fields

**Customer Information**
- Customer Name (required)
- Email Address (required)
- Phone Number (required)

**Order Status** (Important!)
```
Select from 9 options:
- Pending
- Payment Received
- Processing
- Confirmed ← Sets confirmed_at timestamp
- Packed
- Shipped ← Sets shipped_at timestamp
- Out for Delivery
- Delivered ← Sets delivered_at timestamp
- Cancelled
```

**Payment Status**
```
Select from 4 options:
- Pending
- Paid
- Failed
- Refunded
```

**Order Notes**
- Internal notes (not visible to customer)
- For team reference only

### Read-Only Information

These cannot be edited directly:
- Order Number
- Order Date
- Subtotal, Tax, Shipping Amounts
- Grand Total
- Amount Paid/Due

### Saving Changes

1. Make your changes
2. Scroll to bottom
3. Click [Save Changes]
4. See success message
5. Auto-timestamps update based on status change

### Auto-Timestamp Feature

```
When you change status to:
- "Confirmed" → confirmed_at = NOW()
- "Shipped" → shipped_at = NOW()
- "Delivered" → delivered_at = NOW()

This auto-populates the timeline!
```

---

## 🎯 Common Tasks

### Task: Check Pending Orders

1. Go to Dashboard
2. Look at "Pending Orders" card (shows count)
3. Click card to filter pending orders
4. Or go to Orders List → Status Filter → Pending

### Task: Process an Order

1. Open Order Details
2. Click [Edit Order]
3. Change status: Pending → Payment Received → Processing → Confirmed
4. Update payment status if needed
5. Add any notes
6. Save Changes
7. Back to details to see timeline update

### Task: Ship an Order

1. Edit Order
2. Change status to "Shipped"
3. Click Save (shipped_at timestamp auto-set)
4. Customer can see order shipped in timeline

### Task: Mark as Delivered

1. Edit Order
2. Change status to "Delivered"
3. Click Save (delivered_at timestamp auto-set)
4. Order moves to completed section

### Task: Cancel an Order

1. Edit Order
2. Change status to "Cancelled"
3. Update payment status to "Refunded" (if applicable)
4. Add reason in notes: "Cancelled due to..."
5. Save Changes

### Task: Search Specific Customer Orders

1. Go to Orders List
2. In search box, type customer name or email
3. Press Enter
4. See all orders from that customer

### Task: View Only Paid Orders

1. Go to Orders List
2. Payment Status Filter → "Paid"
3. See all paid orders

### Task: Generate Report (Print Order)

1. Open Order Details
2. Click [Print] button
3. Browser print dialog opens
4. Choose Print or Save as PDF
5. Share with customer or keep record

---

## 📊 Dashboard Tips & Tricks

### Revenue Chart Tips
- Hover over bars to see exact amount
- Represents paid/delivered orders only
- Updated daily automatically
- Shows last 7 days trend

### Status Distribution Tips
- Percentages show proportion of total orders
- Higher pending = need to process more
- High cancelled = investigate cancellation reasons

### Recent Orders Tips
- Always sorted by newest first
- Click [View All Orders →] to see more
- Quick way to monitor latest activity

### Quick Actions
- Directly view/edit from dashboard table
- No need to go to full list page
- Faster workflow for frequent tasks

---

## ⚠️ Important Notes

### Cannot Change Directly
- Order Number (auto-generated at checkout)
- Order Date (transaction timestamp)
- Subtotal, Tax, Shipping (calculated at checkout)
- Grand Total (sum of all charges)

### Auto-Timestamps
- confirmed_at, shipped_at, delivered_at update automatically
- Based on status change
- Visible in Order Timeline

### Payment Status
- Independent of Order Status
- Can be paid before order ships
- Update if manual payment received

### Notes
- Internal use only
- Not visible to customer
- Use for team communication

---

## 🔍 Troubleshooting

### Order Not Found
- Check if order ID is correct
- Verify order exists in database
- Refresh page

### Changes Not Saving
- Check for required fields (marked with *)
- Ensure you have admin privileges
- Try again after refresh

### Search Not Working
- Make sure to press Enter in search box
- Check spelling of search term
- Try selecting a filter instead

### Timeline Not Updating
- Refresh page after edit
- Check that status actually changed
- Timeline only shows completed milestones

---

## 📱 Mobile Access

**Note**: Dashboard is fully responsive!

- Open on phone/tablet
- All features available
- Touch-friendly buttons
- Single-column layout
- Easy navigation

---

## 🔐 Security Notes

- All admin functions require login
- SQL injection protected
- Session validates on each page
- Changes logged in database
- Recommend strong admin passwords

---

**Last Updated**: May 7, 2026
**Status**: ✅ Production Ready
**Support**: Reference ADMIN_DASHBOARD_DOCUMENTATION.md for detailed info
