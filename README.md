# Vaccine Module

Vaccine campaign management module for Alpro Pharmacy — Octopus Dashboard (ODB).
Manages flu vaccine appointments, tracks vaccination transactions, and coordinates between pharmacy outlets and partner clinics.

---

## Requirements

- PHP 5.3
- MySQL / MariaDB
- XAMPP (Windows)
- Parent ODB system at `C:\xampp\htdocs\odb`

---

## Database Setup

Run the following SQL on the `odb` database before use.

### 1. Add campaign type and status columns

```sql
ALTER TABLE vaccine_campaign
    ADD COLUMN type   tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=HQ initiated, 2=Outlet initiated',
    ADD COLUMN status tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=Waiting ack, 1=Acknowledged, 2=Cancelled';
```

### 2. Make batch_num and expiry_date nullable

```sql
ALTER TABLE vaccine_trans
    MODIFY COLUMN batch_num   varchar(255) NULL DEFAULT NULL,
    MODIFY COLUMN expiry_date date         NULL DEFAULT NULL;
```

### 3. Create vaccine_trans (if not exists)

All vaccine_ prefixed files write to `vaccine_trans`. Ensure this table exists and mirrors the structure of the original `vaccine_trans` table.

> **Note:** The `vaccine_campaign` table does **not** have a `recycle` column. Do not add `AND recycle=0` to any query against it.

---

## Menu Update

Update the vaccine section in your ODB navigation menu:

```php
<div class="content">
    <ul>
        <?php if($vaccine_autho==1){ ?>
        <li>
            <a href="vaccination/vaccine_index_item.php" title='Click here'>Item Code</a>
        </li>
        <?php } ?>
        <li>
            <a href="vaccination/vaccine_index_clinic.php" title='Click here'>Clinic</a>
        </li>
        <li>
            <a href='vaccination/vaccine_invoice.php'>Add</a> | <a href='vaccination/vaccine_import.php'>Import</a> | <a href='vaccination/vaccine_index.php'>List</a>
        </li>
        <li>
            <a href='vaccination/vaccine_calendar.php'>Calendar</a> | <a href='vaccination/vaccine_calendar.php'>Campaign</a>
        </li>
    </ul>
</div>
```

---

## Key Files

| File | Purpose |
|---|---|
| `vaccine_invoice.php` | New transaction entry — retrieves Xilnex invoice, generates pre-filled transaction forms |
| `vaccine_index.php` | Transaction list with inline status/remark editing |
| `vaccine_update.php` | Edit an existing transaction |
| `vaccine_calendar.php` | Monthly campaign calendar — click date to add, click entry to view |
| `vaccine_campaign.php` | Campaign detail: info, status management, transaction list |
| `vaccine_add_campaign.php` | Add campaign form |
| `vaccine_update_campaign.php` | Edit campaign (permission-gated by campaign type) |
| `vaccine_import.php` | Bulk CSV import — auto-registers customers, resolves/creates campaigns per outlet+date |
| `vaccine_index_clinic.php` | Clinic list |
| `vaccine_add_clinic.php` / `vaccine_update_clinic.php` | Clinic CRUD |
| `vaccine_deactivated_clinic.php` | Deactivated clinics |
| `vaccine_index_item.php` | Vaccine item code management |
| `vaccine_add_code.php` / `vaccine_update_code.php` | Item code CRUD |
| `vaccine_export.php` / `vaccine_download.php` | Export clinic list to .xlsx |
| `vaccine_print_option.php` / `vaccine_print_form.php` | Print-friendly vaccination form |

---

## Campaign Type / Status Logic

| Who creates | `type` | Initial `status` |
|---|---|---|
| HQ (`vaccine_autho=1`) | `1` | `0` — Waiting for Outlet Acknowledgement |
| Outlet staff | `2` | `1` — Auto-acknowledged |

| Status value | Meaning |
|---|---|
| `0` | Waiting for Outlet Acknowledgement |
| `1` | Acknowledged / Active |
| `2` | Cancelled |

---

## User Flow

```
vaccine_calendar.php
  |-- Click date  -->  vaccine_add_campaign.php?v_date=YYYY-MM-DD
  |                         --> vaccine_save_campaign.php  -->  vaccine_campaign.php?id=X
  |-- Click campaign  -->  vaccine_campaign.php?id=X
                                |-- Status update (AJAX)  -->  vaccine_ajax_update_campaign.php
                                |-- Add Transaction  -->  vaccine_invoice.php?campaign_date=YYYY-MM-DD
                                |-- Edit Campaign  -->  vaccine_update_campaign.php?id=X
```

---

## Sensitive Files

The following files contain API credentials and are excluded from this repository. Place them in the same directory as the other vaccine_ files:

- `vaccine_index.php` — Nexus Health integration signing key
- `vaccine_invoice.php` — Xilnex API app ID and token

---

## Errors

If you encounter any errors, please log them in **Fixit**.
