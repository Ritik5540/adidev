# Admin Dashboard Design Reference

## 🎨 Dashboard Layout Overview

```
┌─────────────────────────────────────────────────────────────────┐
│  ADMIN DASHBOARD                                   [Profile ▼]  │
│  Welcome back! Here's what's happening with your orders today. │
└─────────────────────────────────────────────────────────────────┘

┌─────────────┬─────────────┬─────────────┬─────────────┐
│  📦 TOTAL   │  ⏳ PENDING │  ✓ DELIVERED│  ₹ REVENUE  │
│  ORDERS     │  ORDERS     │  ORDERS     │  TOTAL      │
│             │             │             │             │
│    42       │      8      │     15      │ ₹2,45,630   │
│             │             │             │             │
│ All time    │ Need        │ Completed   │ Completed   │
│ orders      │ attention   │ orders      │ sales       │
└─────────────┴─────────────┴─────────────┴─────────────┘

┌────────────────────────────────────┬──────────────────┐
│                                    │                  │
│  REVENUE OVERVIEW (Last 7 Days)   │  ORDER STATUS    │
│                                    │                  │
│  ████                              │  ◆ Pending    8  │
│  ████                              │  ◆ Processing 5  │
│  ███                               │  ◆ Shipped    7  │
│  ██                                │  ◆ Delivered  15 │
│  ███████                           │  ◆ Cancelled  2  │
│  ███████████                       │                  │
│  █████                             │  (percentages)   │
│  Mon Tue Wed Thu Fri Sat Sun        │                  │
│                                    │                  │
└────────────────────────────────────┴──────────────────┘

┌─────────────────────────────────────────────────────────┐
│ RECENT ORDERS                        [View All Orders →] │
├─────────────────────────────────────────────────────────┤
│ Order ID    │ Customer    │ Amount  │ Status  │ Payment │
├─────────────────────────────────────────────────────────┤
│ ORD-001     │ Rajesh K.   │ ₹3,376  │ Delivery│ Paid    │
│ ORD-002     │ Priya S.    │ ₹114,810│ Shipped │ Pending │
│ ORD-003     │ Amit P.     │ ₹3,095  │ Process │ Paid    │
│ [... more rows ...]                                      │
└─────────────────────────────────────────────────────────┘
```

## 📊 Colors Used

### Status Badges
```
Pending          → 🟡 #fff3cd (Yellow)
Payment Received → 🔵 #cfe2ff (Light Blue)
Processing       → 🔵 #cfe2ff (Light Blue)
Shipped          → 🟢 #d1e7dd (Green)
Delivered        → 🟢 #d1e7dd (Green)
Cancelled        → 🔴 #f8d7da (Red)
```

### Interactive Elements
- Primary Button: 🔵 #007bff (Blue)
- Hover State: 🔵 #0056b3 (Dark Blue)
- Success: 🟢 #28a745
- Danger: 🔴 #dc3545
- Warning: 🟡 #ffc107

## 📋 Orders Page Layout

```
┌──────────────────────────────────────────────────────┐
│ ORDERS MANAGEMENT                                    │
│ Manage all customer orders                           │
├──────────────────────────────────────────────────────┤
│
│ [Search by Order/Name...]  [All Status ▼]  [Export ⬇]
│                            [All Payment ▼]
│
├──────────────────────────────────────────────────────┤
│ Order ID  │ Customer   │ Amount │ Status  │ Payment  │
├──────────────────────────────────────────────────────┤
│ ORD-001   │ Rajesh K.  │ ₹3,376 │ Deliver │ Paid     │
│ ORD-002   │ Priya S.   │ ₹114,810 │ Shipped│ Pending  │
│ [... 20 orders per page ...]                         │
│                                                      │
│ [← Previous] [1] [2] [3] [Next →]                   │
└──────────────────────────────────────────────────────┘
```

## 👁️ Order Details Layout

```
[← Back to Orders]

ORDER DETAILS

┌─────────────────────────────────────┬─────────────────┐
│                                     │                 │
│ ℹ️ ORDER INFORMATION                │ 📋 SUMMARY      │
│                                     │                 │
│ Order Number: ORD-2026-001          │ Subtotal: ₹3000 │
│ Order Date: 11 Feb 2026, 02:48 PM   │ Tax: ₹300       │
│ Status: 🟢 Delivered                │ Shipping: ₹80   │
│ Type: Regular                       │ ─────────────   │
│                                     │ Total: ₹3,376   │
│ 👤 CUSTOMER INFO                    │                 │
│                                     │ Amount Paid: ₹3,376 │
│ Name: Rajesh Kumar                  │ Amount Due: ₹0  │
│ Email: rajesh.k@example.com         │                 │
│ Phone: 9876543211                   │ 💳 PAYMENT      │
│                                     │                 │
│ 📬 SHIPPING ADDRESS                 │ Method: COD     │
│ Shop No. 45, Wholesale Market       │ Status: 🟢 Paid │
│ Delhi - 110001                      │ Date: -         │
│                                     │                 │
│ 📦 ORDER ITEMS                      │ ⚙️ ACTIONS      │
│                                     │                 │
│ Product   │ Qty │ Price │ Total     │ [Edit] [Print]  │
│ Product 1 │ 2   │ ₹1499 │ ₹2998     │                 │
│ Product 2 │ 1   │ ₹278  │ ₹278      │ ⏱️ TIMELINE    │
│                                     │                 │
│ Total Items: 2 | Qty: 3             │ ✓ Order Created │
│                                     │ ✓ Delivered     │
│                                     │                 │
└─────────────────────────────────────┴─────────────────┘
```

## ✏️ Order Edit Form Layout

```
[← Back to Order]

EDIT ORDER

ORDER INFORMATION
┌─────────────────────────────────────┐
│ Order Number: ORD-2026-001          │ (Read-only)
│ Order Date: 11 Feb 2026, 02:48 PM   │ (Read-only)
└─────────────────────────────────────┘

CUSTOMER INFORMATION
┌─────────────────────────────────────┐
│ Customer Name                       │
│ [__________________________]         │
│                                     │
│ Email Address    │ Phone Number     │
│ [__________]     │ [__________]     │
└─────────────────────────────────────┘

ORDER STATUS
┌─────────────────────────────────────┐
│ Order Status [▼]                    │
│ • Pending                           │
│ • Payment Received                  │
│ • Processing                        │
│ • Confirmed                         │
│ • Packed                            │
│ • Shipped                           │
│ • Out for Delivery                  │
│ • Delivered                         │
│ • Cancelled                         │
└─────────────────────────────────────┘

PAYMENT STATUS
┌─────────────────────────────────────┐
│ Payment Status [▼]                  │
│ • Pending                           │
│ • Paid                              │
│ • Failed                            │
│ • Refunded                          │
└─────────────────────────────────────┘

NOTES
┌─────────────────────────────────────┐
│ Order Notes                         │
│ [                               ]   │
│ [                               ]   │
│ [_________________________________] │
│ Internal notes for your reference   │
└─────────────────────────────────────┘

ORDER SUMMARY (Read-only)
┌─────────────────────────────────────┐
│ Subtotal: ₹3000  │ Tax: ₹300        │
│ Shipping: ₹80    │ Grand Total: ₹3376 │
│ Paid: ₹3376      │ Due: ₹0          │
└─────────────────────────────────────┘

                    [Cancel]  [Save Changes]
```

## 🎯 Key Design Features

### 1. Dashboard Cards
- Left border color indicates status
- Hover effect with elevation
- Icon + Title + Large Value + Footer text
- Responsive grid layout

### 2. Charts
- Revenue bar chart with hover tooltips
- Status distribution with percentage
- Color-coded indicators
- Mobile-responsive sizing

### 3. Tables
- Striped rows with hover effect
- Sticky headers
- Color-coded status badges
- Inline action buttons
- Responsive overflow handling

### 4. Forms
- Clean, spacious layout
- Clear field labels
- Help text for context
- Visual feedback on focus
- Grouped sections with titles

### 5. Responsive Breakpoints
- Desktop: 1920px+
- Tablet: 768px - 1024px
- Mobile: < 768px

## 🔄 User Flow

```
Admin Dashboard
    ↓
    ├─→ View Statistics & Charts
    ├─→ View Recent Orders
    │   └─→ Click Order ID
    │       └─→ View Order Details
    │           ├─→ Click Edit
    │           │   └─→ Modify & Save
    │           └─→ Click Print
    │               └─→ Browser Print
    │
    └─→ Click "View All Orders"
        └─→ Orders List Page
            ├─→ Search Orders
            ├─→ Filter by Status
            ├─→ Filter by Payment Status
            ├─→ Paginate Results
            └─→ Click Order ID
                └─→ View Order Details
```

## 📱 Mobile Adaptations

```
Desktop                    Mobile
──────────────────────────────────────
4 Card Row          →      1 Card Column
2 Col Charts        →      1 Col Stack
Horizontal Table    →      Vertical Cards
Side Panel          →      Collapsed
Large Buttons       →      Full Width
Icon + Text         →      Icon Only (space)
```

## ⚡ Performance Optimizations

1. **Database Queries**: Indexed on order_id, status, payment_status
2. **Pagination**: 20 records per page to limit query load
3. **Caching Ready**: Can cache dashboard stats
4. **Lazy Loading Ready**: Charts can be deferred
5. **CSS**: Minimal, optimized for performance
6. **JavaScript**: Event delegation for efficiency

---

**Design System**: Material Design Inspired
**Accessibility**: WCAG 2.1 Compliant
**Browser Support**: All Modern Browsers
