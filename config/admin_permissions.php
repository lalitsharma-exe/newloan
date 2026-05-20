<?php

/**
 * All available admin permissions grouped by section.
 * Each key is the permission slug used in middleware and checks.
 * The value is the human-readable label shown in the role editor.
 */
return [
    'Dashboard' => [
        'dashboard' => 'View Dashboard',
    ],

    'Lending' => [
        'applications.view'    => 'View Applications',
        'applications.manage'  => 'Approve / Reject / Edit Applications',
        'loans.view'           => 'View Loans',
        'loans.manage'         => 'Edit Loan Details',
        'loans.disburse'       => 'Disburse Loans',
        'payments.view'        => 'View Payments',
        'payments.manage'      => 'Record / Verify Payments',
        'credit_bureau'        => 'Credit Bureau Access',
        'compuscan'            => 'Compuscan (CCI) Access',
        'financial.intelligence' => 'Financial Intelligence Access',
        'financial.transfers'    => 'Internal Transfers Access',
        'investors.partners'     => 'Investor Partners Access',
        'investments.tranches'   => 'Investment Tranches Access',
    ],

    'Bill Payments' => [
        'mybill'               => 'MyBill Management',
        'myfloat'              => 'MyFloat Access',
    ],

    'Reports & Marketing' => [
        'reports'              => 'View Reports',
        'reports.three_tier'   => '3-Tier Financials Access',
        'cbl.complaints'       => 'CBL Complaints Access',
        'decline.tracker'      => 'Decline Tracker Access',
        'referrals.view'       => 'View Referrals',
        'referrals.manage'     => 'Pay Out Referral Commissions',
        'bulk_sms'             => 'Bulk SMS Campaigns',
    ],

    'Configuration' => [
        'users.view'           => 'View Admin Users',
        'users.manage'         => 'Create / Edit / Deactivate Users',
        'roles.manage'         => 'Manage Roles & Permissions',
        'products.manage'      => 'Manage Loan Products',
        'banks.manage'         => 'Manage Banks & Branches',
        'settings'             => 'System Settings',
    ],

    'System' => [
        'notifications'        => 'View Notifications',
        'audit_log'            => 'View Audit Log',
    ],
];
