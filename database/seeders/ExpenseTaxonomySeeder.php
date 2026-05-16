<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExpenseCategory;
use App\Models\ExpenseSubcategory;
use App\Models\ExpenseTaxonomyItem;

class ExpenseTaxonomySeeder extends Seeder
{
    public function run(): void
    {
        $taxonomy = [
            'Staff Expenses' => [
                'ref' => '1',
                'subs' => [
                    'Salaries & Wages' => ['ref' => '1.1', 'items' => ['Basic monthly salaries', 'Overtime payments', 'Part-time wages', 'Temporary staff payments']],
                    'Allowances' => ['ref' => '1.2', 'items' => ['Transport allowance', 'Housing allowance', 'Airtime allowance', 'Internet / data allowance', 'Meal allowance', 'Risk allowance (field collectors)']],
                    'Commissions & Incentives' => ['ref' => '1.3', 'items' => ['Loan disbursement commission', 'Collection incentives', 'Recovery bonuses', 'Sales commissions', 'Performance bonuses']],
                    'Statutory Contributions' => ['ref' => '1.4', 'items' => ['Pension contributions', 'Social security contributions', 'Workers compensation', 'PAYE tax obligations paid by employer']],
                    'Recruitment Costs' => ['ref' => '1.5', 'items' => ['Job advertisements', 'Recruitment agency fees', 'Interview expenses', 'Background checks', 'Medical examinations']],
                    'Training & Development' => ['ref' => '1.6', 'items' => ['Staff training workshops', 'Compliance training', 'System / software training', 'Conferences and seminars', 'Certification programs']],
                    'Staff Welfare' => ['ref' => '1.7', 'items' => ['Tea / refreshments', 'Staff events', 'Uniforms', 'Funeral contributions', 'Wellness programs']],
                    'Travel & Field Expenses' => ['ref' => '1.8', 'items' => ['Fuel for field officers', 'Vehicle reimbursements', 'Accommodation during trips', 'Daily travel claims', 'Public transport reimbursements']],
                    'Staff Communication Expenses' => ['ref' => '1.9', 'items' => ['Staff mobile phones', 'Communication devices']],
                    'Leave & Benefits' => ['ref' => '1.10', 'items' => ['Annual leave pay', 'Sick leave', 'Maternity leave', 'Leave encashment', 'Severance packages']],
                    'Insurance' => ['ref' => '1.11', 'items' => ['Medical aid', 'Group life insurance', 'Accident cover']],
                    'Office Support for Staff' => ['ref' => '1.12', 'items' => ['Laptops / computers', 'Office chairs / desks', 'Stationery', 'ID cards', 'Staff system accounts / licenses']],
                    'HR & Administration Costs' => ['ref' => '1.13', 'items' => ['HR software', 'Payroll processing', 'Biometric attendance systems', 'Employee records management']],
                    'Staff Loan Costs' => ['ref' => '1.14', 'items' => ['Employee loan benefits', 'Staff loan write-offs', 'Subsidized interest costs']],
                ]
            ],
            'Technology Expenses' => [
                'ref' => '2',
                'subs' => [
                    'Software Development' => ['ref' => '2.1', 'items' => ['LMS (Loan Management System) development', 'Mobile app development', 'Website development', 'API integrations', 'USSD development', 'WhatsApp integration development', 'System upgrades and enhancements', 'Bug fixing and maintenance']],
                    'Server & Hosting Costs' => ['ref' => '2.2', 'items' => ['Cloud hosting (AWS / Azure / DigitalOcean / Hetzner)', 'VPS servers', 'Dedicated servers', 'Database hosting', 'Backup servers', 'Storage services', 'CDN services']],
                    'Domain & Website Costs' => ['ref' => '2.3', 'items' => ['Domain registration (e.g. myloan.co.ls renewal)', 'SSL certificates', 'Website hosting', 'DNS management']],
                    'Internet & Connectivity' => ['ref' => '2.4', 'items' => ['Office internet', 'Backup internet lines', 'Mobile data for operations', 'Branch connectivity', 'VPN services']],
                    'Communication Systems' => ['ref' => '2.5', 'items' => ['WhatsApp Business API', 'SMS gateway charges', 'Bulk SMS services', 'Email systems', 'Call centre systems', 'VoIP services']],
                    'Software Licenses & Subscriptions' => ['ref' => '2.6', 'items' => ['Microsoft 365', 'Google Workspace', 'Antivirus software', 'Accounting software', 'CRM systems', 'Payroll systems', 'PDF / signature tools', 'AI tools and subscriptions']],
                    'Cybersecurity Expenses' => ['ref' => '2.7', 'items' => ['Firewalls', 'Antivirus', 'Penetration testing', 'Security audits', 'Multi-factor authentication systems', 'Endpoint protection', 'Security monitoring tools']],
                    'Backup & Disaster Recovery' => ['ref' => '2.8', 'items' => ['Automated backups', 'Cloud backup storage', 'Disaster recovery systems', 'Redundancy infrastructure']],
                    'IT Support & Maintenance' => ['ref' => '2.9', 'items' => ['IT consultants', 'Technical support contracts', 'Hardware maintenance', 'System monitoring', 'Emergency support']],
                    'Hardware & Devices' => ['ref' => '2.10', 'items' => ['Laptops', 'Desktop computers', 'Printers', 'Routers', 'Network switches', 'Biometric devices', 'Tablets for field agents', 'UPS and power backup systems']],
                    'Payment Integration Costs' => ['ref' => '2.11', 'items' => ['Payment gateway fees', 'Bank integration costs', 'Mobile money integration', 'Transaction processing fees']],
                    'Digital Identity & Verification' => ['ref' => '2.12', 'items' => ['KYC verification systems', 'OCR systems', 'Facial recognition', 'Biometric verification', 'Credit bureau integrations']],
                    'AI & Automation Costs' => ['ref' => '2.13', 'items' => ['AI assistant subscriptions', 'OCR automation', 'Chatbots', 'Workflow automation tools', 'Data analytics systems', 'Reporting dashboards']],
                ]
            ],
            'Operational Expenses' => [
                'ref' => '3',
                'subs' => [
                    'Office & Branch Expenses' => ['ref' => '3.1', 'items' => ['Office rent', 'Cleaning services', 'Office furniture', 'Office maintenance', 'Security services', 'Parking fees']],
                    'Utilities' => ['ref' => '3.2', 'items' => ['Electricity', 'Water', 'Generator fuel', 'Waste disposal']],
                    'Transportation & Fleet' => ['ref' => '3.3', 'items' => ['Fuel', 'Vehicle maintenance', 'Vehicle insurance', 'Tyre replacement', 'Vehicle tracking systems', 'Car wash expenses', 'Driver expenses']],
                    'Communication Expenses' => ['ref' => '3.4', 'items' => ['Telephone bills', 'Bulk SMS', 'Email services', 'Customer notification systems']],
                    'Professional Services' => ['ref' => '3.5', 'items' => ['Legal fees', 'Accounting services', 'Consulting fees']],
                    'Insurance Expenses' => ['ref' => '3.6', 'items' => ['Office insurance', 'Asset insurance']],
                    'Stationery & Consumables' => ['ref' => '3.7', 'items' => ['Printing paper', 'Toners', 'Pens and files', 'Receipt books', 'Printer maintenance']],
                    'Security & Cash Handling' => ['ref' => '3.8', 'items' => ['Cash transportation', 'Armed response services', 'CCTV systems', 'Alarm systems', 'Safe maintenance']],
                    'Miscellaneous Operational Costs' => ['ref' => '3.9', 'items' => ['Donations', 'Penalties / fines', 'Emergency operational expenses', 'Community engagement expenses']],
                ]
            ],
            'Marketing Expenses' => [
                'ref' => '4',
                'subs' => [
                    'Digital Advertising' => ['ref' => '4.1', 'items' => ['Facebook ads', 'Instagram ads', 'Google ads', 'TikTok ads', 'YouTube ads', 'Sponsored posts']],
                    'Traditional Advertising' => ['ref' => '4.2', 'items' => ['Radio advertising', 'Newspaper advertising', 'TV advertising', 'Billboards', 'Street banners', 'Flyers and pamphlets']],
                    'Social Media Management' => ['ref' => '4.3', 'items' => ['Content creation', 'Graphic design', 'Video production', 'Social media managers', 'Photography', 'Community management']],
                    'Branding & Promotional Materials' => ['ref' => '4.4', 'items' => ['Branded clothing', 'T-shirts and caps', 'Banners', 'Pull-up stands', 'Stickers', 'Vehicle branding', 'Office branding']],
                    'Campaign Expenses' => ['ref' => '4.5', 'items' => ['Loan promotion campaigns', 'Seasonal campaigns', 'Referral campaigns', 'Cashback promotions', 'Client activation campaigns']],
                    'Customer Acquisition Costs' => ['ref' => '4.6', 'items' => ['Lead generation', 'Referral incentives', 'Agent commissions', 'Affiliate marketing', 'Sales activation teams']],
                    'Events & Activations' => ['ref' => '4.7', 'items' => ['Roadshows', 'Public activations', 'Community events', 'Financial literacy events', 'Trade fairs', 'Sponsorships']],
                    'Communication & Engagement' => ['ref' => '4.8', 'items' => ['Bulk SMS campaigns', 'WhatsApp campaigns', 'Email marketing', 'Push notifications', 'Customer surveys']],
                    'Website & Digital Presence' => ['ref' => '4.9', 'items' => ['Website maintenance', 'SEO services', 'Landing pages', 'Online forms', 'Analytics tools']],
                    'Public Relations (PR)' => ['ref' => '4.10', 'items' => ['Media relations', 'Press releases', 'Reputation management', 'Corporate communication']],
                ]
            ],
            'Collection Expenses' => [
                'ref' => '5',
                'subs' => [
                    'Field Collection Expenses' => ['ref' => '5.1', 'items' => ['Fuel for collectors', 'Public transport reimbursements', 'Field travel allowances', 'Vehicle usage costs', 'Accommodation during recovery trips']],
                    'Collection Staff Costs' => ['ref' => '5.2', 'items' => ['Collection officer salaries', 'Recovery commissions', 'Incentives for successful collections', 'Overtime payments']],
                    'Communication Costs' => ['ref' => '5.3', 'items' => ['Collection SMS notifications', 'Reminder calls', 'WhatsApp follow-ups', 'Email reminders', 'Bulk reminder systems']],
                    'Legal & Recovery Costs' => ['ref' => '5.4', 'items' => ['Lawyer fees', 'Court filing costs', 'Debt recovery agencies', 'Legal notices', 'Garnishee processing', 'Tracing services']],
                    'Skip Tracing & Investigation' => ['ref' => '5.5', 'items' => ['Borrower tracing tools', 'Investigation costs', 'Address verification', 'Employment verification']],
                    'Repossession Expenses' => ['ref' => '5.6', 'items' => ['Asset repossession', 'Storage fees', 'Auction costs', 'Security during repossession']],
                    'Payment Processing Costs' => ['ref' => '5.7', 'items' => ['Mobile money charges', 'Bank transaction fees', 'Payment gateway charges', 'POS charges']],
                    'Technology & Collection Systems' => ['ref' => '5.8', 'items' => ['Collection software', 'Dialer systems', 'SMS gateway', 'Field collection apps', 'GPS tracking systems']],
                    'Third-Party Collection Costs' => ['ref' => '5.9', 'items' => ['External collectors', 'Collection agencies', 'Commission-based recovery partners']],
                    'Settlement & Negotiation Costs' => ['ref' => '5.10', 'items' => ['Settlement administration', 'Restructuring processing', 'Documentation costs']],
                ]
            ],
            'Finance Expenses' => [
                'ref' => '6',
                'subs' => [
                    'Interest Expenses' => ['ref' => '6.1', 'items' => ['Interest paid on borrowed funds', 'Interest on investor capital', 'Interest on bank overdrafts', 'Interest on credit facilities', 'Interest on shareholder loans']],
                    'Bank Charges & Transaction Fees' => ['ref' => '6.2', 'items' => ['Monthly bank charges', 'EFT charges', 'RTGS charges', 'ATM charges', 'Mobile money transaction fees', 'Merchant fees']],
                    'Loan Funding Costs' => ['ref' => '6.3', 'items' => ['Wholesale lending costs', 'Institutional funding costs', 'Credit line charges', 'Syndicated financing fees']],
                    'Payment Processing Expenses' => ['ref' => '6.4', 'items' => ['Payment gateway charges', 'POS transaction fees', 'Collection processing fees', 'API transaction charges']],
                    'Foreign Exchange (FX) Costs' => ['ref' => '6.5', 'items' => ['Currency conversion losses', 'International transfer fees', 'FX fluctuations']],
                    'Treasury & Liquidity Management Costs' => ['ref' => '6.6', 'items' => ['Liquidity reserve costs', 'Cash handling charges', 'Cash transportation fees', 'Investment management fees']],
                    'Penalties & Financial Fines' => ['ref' => '6.7', 'items' => ['Late payment penalties', 'Regulatory penalties', 'Bank penalties', 'Tax penalties']],
                    'Audit & Financial Compliance' => ['ref' => '6.8', 'items' => ['External audit fees', 'Financial compliance costs', 'Tax consulting fees', 'Financial reporting services']],
                    'Insurance' => ['ref' => '6.9', 'items' => ['Credit life insurance', 'Loan protection insurance', 'Fidelity guarantee insurance', 'Cash-in-transit insurance']],
                    'Provisioning & Credit Loss Expenses' => ['ref' => '6.10', 'items' => ['Bad debt provisions', 'Impairment expenses', 'Write-offs', 'Expected credit loss (ECL) adjustments']],
                    'Investment & Capital Raising Costs' => ['ref' => '6.11', 'items' => ['Investor relations costs', 'Due diligence costs', 'Legal documentation for funding', 'Capital raising advisory fees']],
                    'Tax Expenses' => ['ref' => '6.12', 'items' => ['Corporate tax', 'Withholding tax', 'VAT not recoverable', 'Tax filing fees']],
                ]
            ],
            'Compliance Expenses' => [
                'ref' => '7',
                'subs' => [
                    'Regulatory Licensing Costs' => ['ref' => '7.1', 'items' => ['Microfinance license fees', 'Business license fees', 'Annual license renewals', 'Regulatory application fees', 'Registration fees', 'Compliance filing costs']],
                    'Legal & Regulatory Advisory' => ['ref' => '7.2', 'items' => ['Legal consultants', 'Regulatory consultants', 'Compliance advisors', 'Contract review services']],
                    'Audit & Inspection Costs' => ['ref' => '7.3', 'items' => ['External audits', 'Internal audits', 'Regulatory inspections', 'Compliance assessments', 'Risk audits']],
                    'AML / CFT Compliance Costs' => ['ref' => '7.4', 'items' => ['AML monitoring systems', 'Transaction monitoring', 'Suspicious transaction reporting', 'Sanctions screening', 'PEP screening systems', 'Compliance software']],
                    'KYC & Identity Verification' => ['ref' => '7.5', 'items' => ['ID verification systems', 'Biometric verification', 'Document verification', 'Customer due diligence systems', 'Address verification']],
                    'Data Protection & Privacy Compliance' => ['ref' => '7.6', 'items' => ['Data protection audits', 'Privacy compliance tools', 'Cybersecurity compliance', 'Consent management systems', 'Data retention systems']],
                    'Staff Compliance Training' => ['ref' => '7.7', 'items' => ['AML training', 'Fraud awareness training', 'Regulatory training', 'Data privacy training', 'Ethics training']],
                    'Reporting & Filing Costs' => ['ref' => '7.8', 'items' => ['Regulatory reporting systems', 'Filing fees', 'Statutory submissions', 'Compliance reporting tools']],
                    'Insurance & Risk Compliance' => ['ref' => '7.9', 'items' => ['Professional indemnity insurance', 'Directors & officers insurance', 'Compliance-related insurance']],
                    'Policy & Documentation Management' => ['ref' => '7.10', 'items' => ['Policy development', 'Procedure manuals', 'Legal documentation updates', 'Compliance document management']],
                    'Monitoring & Risk Management Systems' => ['ref' => '7.11', 'items' => ['Risk monitoring software', 'Internal control systems', 'Fraud detection systems', 'Case management systems']],
                    'Penalties & Remediation' => ['ref' => '7.12', 'items' => ['Regulatory fines', 'Compliance remediation costs', 'Investigation expenses', 'Corrective action implementation']],
                ]
            ],
            'Credit & Risk Expenses' => [
                'ref' => '8',
                'subs' => [
                    'Credit Assessment Costs' => ['ref' => '8.1', 'items' => ['Credit analysis staff', 'Loan appraisal costs', 'Affordability assessment', 'Income verification', 'Employment verification', 'Reference checks']],
                    'Credit Bureau & Data Costs' => ['ref' => '8.2', 'items' => ['Credit bureau checks', 'Credit scoring services', 'Borrower data access fees', 'Financial behaviour analytics']],
                    'Risk Management Systems' => ['ref' => '8.3', 'items' => ['Credit scoring systems', 'Risk engines', 'Portfolio monitoring systems', 'Early warning systems', 'Risk dashboards']],
                    'Fraud Prevention Costs' => ['ref' => '8.4', 'items' => ['Fraud detection software', 'Identity verification systems', 'Device fingerprinting', 'Anti-fraud monitoring tools']],
                    'Portfolio Monitoring Costs' => ['ref' => '8.5', 'items' => ['Portfolio analytics', 'Delinquency monitoring', 'Exposure analysis', 'Concentration risk analysis']],
                    'Provisioning & Expected Credit Loss (ECL)' => ['ref' => '8.6', 'items' => ['Bad debt provisioning', 'IFRS 9 expected loss calculations', 'Loan impairment expenses', 'Risk reserve allocations']],
                    'Write-Off & Recovery Losses' => ['ref' => '8.7', 'items' => ['Loan write-offs', 'Settlement losses', 'Recovery shortfalls', 'Collateral losses']],
                    'Internal Risk Audits' => ['ref' => '8.8', 'items' => ['Credit audits', 'Risk reviews', 'Loan file inspections', 'Portfolio stress testing']],
                    'Compliance Risk Costs' => ['ref' => '8.9', 'items' => ['Regulatory risk assessments', 'Credit policy reviews', 'Governance reviews']],
                    'Insurance & Credit Protection' => ['ref' => '8.10', 'items' => ['Portfolio insurance', 'Guarantee schemes', 'Default protection programs']],
                    'Risk Consulting & Advisory' => ['ref' => '8.11', 'items' => ['Risk consultants', 'Model validation experts', 'Credit policy consultants']],
                    'Staff Training for Risk Management' => ['ref' => '8.12', 'items' => ['Credit underwriting training', 'Risk management certification', 'Collection risk training']],
                ]
            ],
            'Bad Debt Expenses' => [
                'ref' => '9',
                'subs' => [
                    'Loan Write-Offs' => ['ref' => '9.1', 'items' => ['Fully defaulted loans removed from the books', 'Loans deemed irrecoverable', 'Charged-off principal amounts', 'Written-off interest amounts']],
                    'Impairment & Provision Expenses' => ['ref' => '9.2', 'items' => ['Expected credit loss (ECL) adjustments', 'Loan impairment provisions', 'Stage 1, 2, and 3 provisioning (IFRS 9)', 'Monthly / quarterly provision increases']],
                    'Partial Recovery Losses' => ['ref' => '9.3', 'items' => ['Shortfalls after settlement', 'Reduced recovery agreements', 'Discounted settlements', 'Haircuts on negotiated repayments']],
                    'Non-Performing Loan Costs' => ['ref' => '9.4', 'items' => ['Costs associated with NPL accounts', 'Increased monitoring costs before write-off', 'Administrative handling of delinquent accounts']],
                    'Collateral Losses' => ['ref' => '9.5', 'items' => ['Asset disposal losses', 'Auction shortfalls', 'Depreciation of repossessed assets', 'Legal recovery losses']],
                    'Legal Recovery Losses' => ['ref' => '9.6', 'items' => ['Court cases with no recovery', 'Failed enforcement actions', 'Legal fees on unrecoverable loans']],
                    'Fraud-Related Credit Losses' => ['ref' => '9.7', 'items' => ['Fraudulent loan approvals', 'Identity theft cases', 'Fake documentation losses', 'Internal fraud losses']],
                    'Collection Exhaustion Costs (Before Write-Off)' => ['ref' => '9.8', 'items' => ['Final recovery attempts before closure', 'Intensive field visits', 'Final demand notices', 'Escalation costs that still result in loss']],
                ]
            ],
            'Cost of Funds' => [
                'ref' => '10',
                'subs' => [
                    'Interest Paid on Borrowings' => ['ref' => '10.1', 'items' => ['Bank loans interest', 'Credit line interest', 'Overdraft interest', 'Institutional funding interest', 'Bond or note interest']],
                    'Investor Return Costs' => ['ref' => '10.2', 'items' => ['Dividends to investors', 'Profit-sharing agreements', 'Equity return expectations (implicit cost)']],
                    'Deposit Costs' => ['ref' => '10.3', 'items' => ['Interest paid on customer savings', 'Fixed deposit interest obligations', 'Savings mobilisation costs']],
                    'Wholesale Funding Costs' => ['ref' => '10.4', 'items' => ['Microfinance wholesale lenders', 'Development finance institutions (DFIs)', 'Syndicated loan interest', 'Funding facility arrangement fees']],
                    'Arrangement & Setup Fees' => ['ref' => '10.5', 'items' => ['Loan origination fees on funding', 'Legal structuring fees', 'Facility setup charges', 'Commitment fees (charged on unused credit lines)']],
                    'Treasury & Liquidity Costs' => ['ref' => '10.6', 'items' => ['Cash holding cost (idle funds)', 'Reserve requirements', 'Opportunity cost of unused capital']],
                    'Hedging & Risk Costs' => ['ref' => '10.7', 'items' => ['Currency hedging costs (FX protection)', 'Interest rate hedging instruments', 'Risk premiums on high-risk funding']],
                    'Administrative Funding Costs' => ['ref' => '10.8', 'items' => ['Fund management fees', 'Investor reporting costs', 'Treasury management systems']],
                ]
            ],
        ];

        foreach ($taxonomy as $catName => $catData) {
            $category = ExpenseCategory::updateOrCreate(
                ['ref_code' => $catData['ref']],
                ['name' => $catName]
            );

            foreach ($catData['subs'] as $subName => $subData) {
                $subcategory = ExpenseSubcategory::updateOrCreate(
                    ['ref_code' => $subData['ref'], 'category_id' => $category->id],
                    ['name' => $subName]
                );

                foreach ($subData['items'] as $index => $itemName) {
                    $itemRef = $subData['ref'] . '.' . ($index + 1);
                    ExpenseTaxonomyItem::updateOrCreate(
                        ['ref_code' => $itemRef, 'subcategory_id' => $subcategory->id],
                        ['name' => $itemName]
                    );
                }
            }
        }
    }
}
