<?php

namespace App\Services\Admin;

use App\Models\Investment;
use Carbon\Carbon;

class ContractService
{
    /**
     * Generate MS Word compatible HTML content for the contract.
     */
    public function generateContractHtml(Investment $investment): string
    {
        $investor = $investment->investor;
        $isMD = $investor->investor_type === 'MD';
        $ratePct = $isMD ? '5%' : '1.5%';
        $typeLabel = $isMD ? 'Managing Director' : 'Public Investor';
        $themeColor = $isMD ? '#312e81' : '#1e3a8a'; // Indigo vs Blue

        $fullName = e($investor->full_name);
        $idNumber = e($investor->id_number);
        $phone = e($investor->phone ?: 'N/A');
        $email = e($investor->email ?: 'N/A');
        $address = e($investor->address ?: 'N/A');
        $bank = e($investor->bank_name ?: 'N/A');
        $accountNo = e($investor->account_number ?: 'N/A');

        $principal = number_format($investment->principal, 2);
        $monthlyInterest = number_format($investment->monthly_interest, 2);
        $totalInterest = number_format($investment->total_interest, 2);
        $totalRepayable = number_format($investment->total_repayable, 2);
        $investmentDate = Carbon::parse($investment->investment_date)->format('d F Y');
        $investmentYear = Carbon::parse($investment->investment_date)->year;

        // Generate Schedule A rows
        $accrualsHtml = '';
        $cumulative = 0;
        foreach ($investment->accruals as $index => $accrual) {
            $cumulative += $accrual->interest;
            $monthNo = $index + 1;
            $accrualMonthEnd = Carbon::parse($accrual->accrual_date)->format('d F Y');
            $interestStr = number_format($accrual->interest, 2);
            $cumulativeStr = number_format($cumulative, 2);
            
            $accrualsHtml .= "
                <tr>
                    <td style='border: 1px solid #cbd5e1; padding: 10px; text-align: center; font-size: 11pt;'>{$monthNo}</td>
                    <td style='border: 1px solid #cbd5e1; padding: 10px; font-size: 11pt;'>{$accrualMonthEnd}</td>
                    <td style='border: 1px solid #cbd5e1; padding: 10px; text-align: right; font-size: 11pt;'>LSL {$interestStr}</td>
                    <td style='border: 1px solid #cbd5e1; padding: 10px; text-align: right; font-size: 11pt;'>LSL {$cumulativeStr}</td>
                </tr>";
        }

        return "
        <html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'>
        <head>
            <title>Investment Contract - {$investment->contract_ref}</title>
            <style>
                body { font-family: 'Segoe UI', Arial, sans-serif; color: #334155; line-height: 1.6; margin: 40px; }
                h1, h2, h3 { color: {$themeColor}; font-weight: bold; }
                table { width: 100%; border-collapse: collapse; margin-top: 15px; margin-bottom: 15px; }
                th { background-color: {$themeColor}; color: white; border: 1px solid {$themeColor}; padding: 10px; font-size: 11pt; text-align: left; }
                .badge { background-color: {$themeColor}; color: white; padding: 8px 15px; border-radius: 4px; font-weight: bold; font-size: 11pt; }
                .cover-page { text-align: center; padding: 100px 20px; }
                .page-break { page-break-after: always; }
                .clause { margin-bottom: 20px; text-align: justify; }
                .clause-title { font-weight: bold; color: #1e293b; margin-top: 15px; font-size: 12pt; }
            </style>
        </head>
        <body>
            <!-- COVER PAGE -->
            <div class='cover-page'>
                <br/><br/><br/><br/>
                <h1 style='font-size: 28pt; margin-bottom: 10px;'>INVESTOR CAPITAL AGREEMENT</h1>
                <h2 style='font-size: 16pt; color: #64748b; font-weight: normal; margin-top: 0;'>Fixed Return Investment  |  {$typeLabel}  |  {$ratePct}/month</h2>
                <h3 style='font-size: 12pt; color: #64748b; font-weight: normal; margin-top: 0;'>Annual Maturity — 31 December</h3>
                <br/><br/><br/><br/>
                <div style='border-top: 2px solid {$themeColor}; width: 100px; margin: 0 auto;'></div>
                <br/><br/><br/><br/>
                <table style='width: 350px; margin: 0 auto; border: none;'>
                    <tr>
                        <td style='padding: 5px; font-weight: bold; text-align: right; width: 180px; font-size: 10pt; color: #64748b;'>CONTRACT REFERENCE NO:</td>
                        <td style='padding: 5px; text-align: left; font-weight: bold; font-size: 10pt;'>{$investment->contract_ref}</td>
                    </tr>
                    <tr>
                        <td style='padding: 5px; font-weight: bold; text-align: right; font-size: 10pt; color: #64748b;'>DATE OF AGREEMENT:</td>
                        <td style='padding: 5px; text-align: left; font-weight: bold; font-size: 10pt;'>{$investmentDate}</td>
                    </tr>
                </table>
                <br/><br/><br/><br/>
                <p style='font-size: 9pt; color: #94a3b8; letter-spacing: 1px;'>STRICTLY CONFIDENTIAL — INTERNAL USE ONLY</p>
            </div>
            
            <div class='page-break'></div>

            <!-- AGREEMENT DETAILS -->
            <h2 style='border-bottom: 2px solid {$themeColor}; padding-bottom: 5px;'>AGREEMENT DETAILS</h2>
            <div style='margin-bottom: 15px; margin-top: 15px; text-align: center;'>
                <span class='badge'>Investor Classification: {$typeLabel} &nbsp;|&nbsp; Interest Rate: {$ratePct} per month</span>
            </div>
            
            <table>
                <tr>
                    <th colspan='2'>The Company</th>
                </tr>
                <tr>
                    <td style='width: 150px; padding: 8px; border: 1px solid #cbd5e1; font-weight: bold; font-size: 11pt;'>Company Name:</td>
                    <td style='padding: 8px; border: 1px solid #cbd5e1; font-size: 11pt;'>MyLoan (Pty) Ltd</td>
                </tr>
                <tr>
                    <td style='padding: 8px; border: 1px solid #cbd5e1; font-weight: bold; font-size: 11pt;'>Registration No:</td>
                    <td style='padding: 8px; border: 1px solid #cbd5e1; font-size: 11pt;'>2026/REG/LSO/0987</td>
                </tr>
                <tr>
                    <td style='padding: 8px; border: 1px solid #cbd5e1; font-weight: bold; font-size: 11pt;'>Registered Address:</td>
                    <td style='padding: 8px; border: 1px solid #cbd5e1; font-size: 11pt;'>45 Kingsway Road, Maseru 100, Lesotho</td>
                </tr>
                <tr>
                    <th colspan='2'>The Investor</th>
                </tr>
                <tr>
                    <td style='padding: 8px; border: 1px solid #cbd5e1; font-weight: bold; font-size: 11pt;'>Full Name:</td>
                    <td style='padding: 8px; border: 1px solid #cbd5e1; font-size: 11pt;'>{$fullName}</td>
                </tr>
                <tr>
                    <td style='padding: 8px; border: 1px solid #cbd5e1; font-weight: bold; font-size: 11pt;'>Investor Type:</td>
                    <td style='padding: 8px; border: 1px solid #cbd5e1; font-size: 11pt;'>{$typeLabel} ({$ratePct} monthly)</td>
                </tr>
                <tr>
                    <td style='padding: 8px; border: 1px solid #cbd5e1; font-weight: bold; font-size: 11pt;'>ID / Passport No:</td>
                    <td style='padding: 8px; border: 1px solid #cbd5e1; font-size: 11pt;'>{$idNumber}</td>
                </tr>
                <tr>
                    <td style='padding: 8px; border: 1px solid #cbd5e1; font-weight: bold; font-size: 11pt;'>Contact Number:</td>
                    <td style='padding: 8px; border: 1px solid #cbd5e1; font-size: 11pt;'>{$phone}</td>
                </tr>
                <tr>
                    <td style='padding: 8px; border: 1px solid #cbd5e1; font-weight: bold; font-size: 11pt;'>Email Address:</td>
                    <td style='padding: 8px; border: 1px solid #cbd5e1; font-size: 11pt;'>{$email}</td>
                </tr>
                <tr>
                    <td style='padding: 8px; border: 1px solid #cbd5e1; font-weight: bold; font-size: 11pt;'>Physical Address:</td>
                    <td style='padding: 8px; border: 1px solid #cbd5e1; font-size: 11pt;'>{$address}</td>
                </tr>
                <tr>
                    <td style='padding: 8px; border: 1px solid #cbd5e1; font-weight: bold; font-size: 11pt;'>Bank Account:</td>
                    <td style='padding: 8px; border: 1px solid #cbd5e1; font-size: 11pt;'>{$bank} &mdash; Acc No: {$accountNo}</td>
                </tr>
            </table>

            <h2 style='border-bottom: 2px solid {$themeColor}; padding-bottom: 5px; margin-top: 30px;'>INVESTMENT TERMS</h2>
            <table>
                <tr>
                    <td style='width: 200px; padding: 8px; border: 1px solid #cbd5e1; font-weight: bold; font-size: 11pt;'>Principal Amount:</td>
                    <td style='padding: 8px; border: 1px solid #cbd5e1; font-size: 11pt;'>LSL {$principal}</td>
                </tr>
                <tr>
                    <td style='padding: 8px; border: 1px solid #cbd5e1; font-weight: bold; font-size: 11pt;'>Investment Date:</td>
                    <td style='padding: 8px; border: 1px solid #cbd5e1; font-size: 11pt;'>{$investmentDate}</td>
                </tr>
                <tr>
                    <td style='padding: 8px; border: 1px solid #cbd5e1; font-weight: bold; font-size: 11pt;'>Interest Start Date:</td>
                    <td style='padding: 8px; border: 1px solid #cbd5e1; font-size: 11pt;'>End of the month following the investment month</td>
                </tr>
                <tr>
                    <td style='padding: 8px; border: 1px solid #cbd5e1; font-weight: bold; font-size: 11pt;'>Maturity Date:</td>
                    <td style='padding: 8px; border: 1px solid #cbd5e1; font-size: 11pt;'>31 December {$investmentYear}</td>
                </tr>
                <tr>
                    <td style='padding: 8px; border: 1px solid #cbd5e1; font-weight: bold; font-size: 11pt;'>Total Interest Months:</td>
                    <td style='padding: 8px; border: 1px solid #cbd5e1; font-size: 11pt;'>{$investment->total_months} months</td>
                </tr>
                <tr>
                    <td style='padding: 8px; border: 1px solid #cbd5e1; font-weight: bold; font-size: 11pt;'>Total Interest Payable:</td>
                    <td style='padding: 8px; border: 1px solid #cbd5e1; font-size: 11pt;'>LSL {$totalInterest}</td>
                </tr>
                <tr>
                    <td style='padding: 8px; border: 1px solid #cbd5e1; font-weight: bold; font-size: 11pt; background-color: #f1f5f9;'>Total Repayable:</td>
                    <td style='padding: 8px; border: 1px solid #cbd5e1; font-size: 11pt; font-weight: bold; background-color: #f1f5f9;'>LSL {$totalRepayable}</td>
                </tr>
            </table>

            <div style='background-color: #f8fafc; border-left: 4px solid {$themeColor}; padding: 15px; margin-top: 15px;'>
                <p style='font-size: 9.5pt; font-style: italic; color: #475569; margin: 0;'>
                    <strong>Worked Example:</strong> An investment made on any date in May begins accruing interest from 30 June. 
                    The investor earns {$investment->total_months} months of interest (June through December) at {$ratePct} per month, 
                    yielding LSL {$monthlyInterest} monthly interest, totaling LSL {$totalInterest} overall interest payable.
                </p>
            </div>

            <div class='page-break'></div>

            <!-- TERMS AND CONDITIONS -->
            <h2 style='border-bottom: 2px solid {$themeColor}; padding-bottom: 5px;'>TERMS AND CONDITIONS</h2>
            
            <div class='clause'>
                <div class='clause-title'>1. Nature of Investment</div>
                The Investor agrees to provide capital to the Company for the purpose of funding the Company's lending operations. This arrangement constitutes a fixed-term, fixed-return capital investment and shall not be construed as an equity stake, partnership interest, or entitlement to ownership in the Company.
                <br/>a. The investment shall be used exclusively to fund loans issued through the Company's lending platform.
                <br/>b. The Investor shall have no claim over the underlying loan assets or borrower repayments.
                <br/>c. The Company retains full operational control and discretion over how the capital is deployed within its lending portfolio.
                <br/>d. The applicable interest rate for this Agreement is {$ratePct} per month, determined by the Investor's classification as a {$typeLabel}. This rate is fixed for the duration of this Agreement and may not be renegotiated after signing.
            </div>

            <div class='clause'>
                <div class='clause-title'>2. Capital Contribution</div>
                The Investor shall deposit the Principal Amount as specified in the Investment Terms section into the Company's designated account on or before the Investment Date. The investment shall be recorded in the Company's system upon receipt of cleared funds.
                <br/>a. The Company shall issue a written confirmation of receipt within 2 business days of the funds clearing.
                <br/>b. The deposit details shall be provided separately in the onboarding confirmation email.
                <br/>c. The investment is irrevocable once funds have been deposited and confirmed, except as otherwise provided in Clause 7 (Early Termination).
            </div>

            <div class='clause'>
                <div class='clause-title'>3. Interest Calculation and Accrual</div>
                Interest shall be calculated as a flat rate of {$ratePct} per month on the Principal Amount and shall accrue as follows:
                <br/>a. Interest accrual begins at the end of the calendar month immediately following the month in which the investment was made, regardless of the specific date within that month on which the investment was made.
                <br/>b. Interest accrues monthly thereafter on the last calendar day of each month until the Maturity Date of 31 December of the Investment Year.
                <br/>c. An investment made in December of any year shall accrue zero months of interest, as maturity falls within the same calendar month. The Company and Investor may by mutual written agreement defer such an investment to the following year.
                <br/>d. Interest is calculated on the original Principal Amount only and does not compound.
                <br/>e. The Total Interest Payable and Total Repayable amounts stated in the Investment Terms are fixed at signing ({$ratePct}/month x total months x principal) and shall not be altered unless by written amendment signed by both parties.
            </div>

            <div class='clause'>
                <div class='clause-title'>4. Repayment</div>
                On the Maturity Date of 31 December of the Investment Year, the Company shall repay the Investor the Total Repayable amount, being the sum of the Principal Amount plus all accrued interest.
                <br/>a. Repayment shall be made by electronic funds transfer to the Investor's bank account as specified in this Agreement within 5 business days of the Maturity Date.
                <br/>b. In the event the Maturity Date falls on a public holiday or weekend, repayment shall be made on the next available business day.
                <br/>c. The Investor shall notify the Company in writing of any change in banking details at least 10 business days before the Maturity Date.
                <br/>d. Upon full repayment, the Company's obligations under this Agreement shall be discharged in full.
            </div>

            <div class='clause'>
                <div class='clause-title'>5. Reinvestment</div>
                Upon maturity, the Investor may elect to reinvest the returned capital for a new investment term. Reinvestment shall be treated as a new agreement and a new contract shall be generated by the system with updated terms.
                <br/>a. Reinvestment is not automatic. The Investor must submit a reinvestment request through the platform or in writing at least 5 business days before the Maturity Date.
                <br/>b. Interest rates applicable to reinvestments shall reflect the Investor's current classification at the time of reinvestment. The {$typeLabel} rate of {$ratePct}/month applies to this agreement only and is not guaranteed for future terms.
            </div>

            <div class='clause'>
                <div class='clause-title'>6. Risk and Acknowledgements</div>
                The Investor acknowledges and agrees to the following:
                <br/>a. The investment is not guaranteed by any government, regulatory body, or deposit insurance scheme.
                <br/>b. The return of principal and payment of interest is subject to the Company's financial performance and operational continuity.
                <br/>c. The Investor has independently assessed the risks of this investment and has not relied solely on representations made by the Company.
                <br/>d. The Company does not guarantee investment returns beyond what is expressly stated in this Agreement.
                <br/>e. The Investor confirms that the funds invested are from a legitimate source and comply with all applicable laws, including anti-money laundering regulations in the Kingdom of Lesotho.
                <br/>f. The Investor acknowledges that the interest rate of {$ratePct} per month is specific to their investor classification ({$typeLabel}) and that different rates apply to other investor categories.
            </div>

            <div class='clause'>
                <div class='clause-title'>7. Early Termination</div>
                This Agreement is intended to run to its full maturity date of 31 December. Early termination is permitted subject to the following conditions, which the Investor accepts by signing this Agreement:
                <br/>a. Notice Period: The Investor must submit a written early termination request to the Company giving no less than 30 calendar days notice. The termination request must state the Investor's full name, contract reference number, and the intended termination date. Notice may be submitted via email to the Company's registered address or through the investment platform.
                <br/>b. Accrual During Notice Period: Interest shall continue to accrue normally throughout the 30-day notice period. Any calendar month-end that falls within the notice period shall be treated as a completed interest month and shall be paid to the Investor.
                <br/>c. Interest Forfeiture: Upon termination, the Investor shall forfeit all interest for any calendar months that had not yet reached their month-end accrual date at the time the termination is processed. Only months where the accrual has been fully posted (status = posted) shall be paid.
                <br/>d. Termination Fee: A termination fee of 10% of the total forfeited interest amount shall be deducted from the Investor's final payout. The termination fee compensates the Company for the cost of returning capital early and the disruption to the lending pool. Formula: Termination Fee = (Forfeited Monthly Interest x Remaining Months) x 10%.
                <br/>e. Payout Calculation: The final amount repayable on early termination shall be calculated as follows: (i) Principal Amount; plus (ii) Interest accrued on all fully posted months; less (iii) Termination fee of 10% of forfeited interest.
                <br/>f. Liquidity Gate: The Company reserves the right to decline an early termination request if processing the repayment would reduce the lending pool balance below its minimum operational threshold. In such a case, the Company shall notify the Investor in writing within 5 business days of the notice period expiry and shall provide an estimated earliest available exit date. The Investor's accruals shall continue until the request is approved.
                <br/>g. Approval and Processing: Following the 30-day notice period, the Company shall process the approved termination within 5 business days. The final payout shall be transferred to the Investor's registered bank account. A termination statement showing the full breakdown of principal, earned interest, forfeited interest, termination fee, and net payout shall be provided.
                <br/>h. Company-Initiated Termination: The Company may terminate this Agreement immediately and without penalty to the Investor in the event of: (i) material breach of this Agreement by the Investor; (ii) provision of false or fraudulent information; (iii) regulatory or legal requirement. In such cases, the Investor shall receive their principal plus all fully posted interest with no termination fee applied.
                <br/>i. No Early Termination in Final Month: No early termination requests shall be accepted within 30 calendar days of the Maturity Date of 31 December, as the standard maturity repayment process shall apply.
            </div>

            <div class='clause'>
                <div class='clause-title'>8. Confidentiality</div>
                Both parties agree to keep the terms of this Agreement strictly confidential and shall not disclose any information relating to this Agreement &mdash; including the applicable interest rate and investor classification &mdash; to any third party without prior written consent from the other party, except as required by law or regulatory authority.
            </div>

            <div class='clause'>
                <div class='clause-title'>9. Governing Law and Disputes</div>
                This Agreement shall be governed by and construed in accordance with the laws of the Kingdom of Lesotho. Any dispute arising out of or in connection with this Agreement shall first be subject to good-faith negotiation between the parties. If unresolved within 30 days, the dispute shall be referred to arbitration in accordance with Lesotho's applicable arbitration legislation.
            </div>

            <div class='clause'>
                <div class='clause-title'>10. Amendments</div>
                No amendment, modification, or waiver of any provision of this Agreement shall be effective unless made in writing and signed by duly authorised representatives of both parties. The investor classification and applicable interest rate may not be amended after the Agreement has been signed.
            </div>

            <div class='clause'>
                <div class='clause-title'>11. Entire Agreement</div>
                This Agreement constitutes the entire agreement between the parties with respect to the subject matter hereof and supersedes all prior negotiations, representations, warranties, and understandings of the parties with respect thereto.
            </div>

            <div class='page-break'></div>

            <!-- SCHEDULE A -->
            <h2 style='border-bottom: 2px solid {$themeColor}; padding-bottom: 5px;'>SCHEDULE A &mdash; INTEREST ACCRUAL SCHEDULE</h2>
            <p style='font-size: 11pt;'>
                The following table sets out the monthly interest accrual schedule for this investment at the {$typeLabel} rate of {$ratePct} per month. 
                Each row represents a calendar month-end on which interest is earned.
            </p>
            
            <table>
                <thead>
                    <tr>
                        <th style='width: 80px; text-align: center;'>Month No.</th>
                        <th>Accrual Month End</th>
                        <th style='text-align: right; width: 180px;'>Interest @ {$ratePct}</th>
                        <th style='text-align: right; width: 180px;'>Cumulative Interest</th>
                    </tr>
                </thead>
                <tbody>
                    {$accrualsHtml}
                    <tr style='background-color: #f1f5f9; font-weight: bold;'>
                        <td colspan='2' style='border: 1px solid #cbd5e1; padding: 10px; font-size: 11pt; text-align: right;'>TOTALS:</td>
                        <td style='border: 1px solid #cbd5e1; padding: 10px; font-size: 11pt; text-align: right;'>LSL {$totalInterest}</td>
                        <td style='border: 1px solid #cbd5e1; padding: 10px; font-size: 11pt; text-align: right;'>LSL {$totalInterest}</td>
                    </tr>
                    <tr style='background-color: #e2e8f0; font-weight: bold;'>
                        <td colspan='3' style='border: 1px solid #cbd5e1; padding: 10px; font-size: 11pt; text-align: right; text-transform: uppercase;'>Total Repayable on 31 December:</td>
                        <td style='border: 1px solid #cbd5e1; padding: 10px; font-size: 11pt; text-align: right; color: {$themeColor};'>LSL {$totalRepayable}</td>
                    </tr>
                </tbody>
            </table>

            <div class='page-break'></div>

            <!-- SIGNATURES -->
            <h2 style='border-bottom: 2px solid {$themeColor}; padding-bottom: 5px; margin-top: 30px;'>EXECUTION AND SIGNATURES</h2>
            <p style='font-size: 11pt;'>
                IN WITNESS WHEREOF, the parties have executed this Agreement as of the date first written above. 
                By signing below, both parties confirm they have read, understood, and agreed to all terms and conditions set out in this Agreement.
            </p>
            
            <br/><br/>
            
            <table style='border: none; margin-top: 40px;'>
                <tr style='border: none;'>
                    <td style='width: 45%; border: none; padding: 0;'>
                        <p style='font-weight: bold; border-bottom: 1px solid #334155; margin-bottom: 5px; padding-bottom: 60px;'></p>
                        <p style='font-size: 10pt; font-weight: bold; margin: 0;'>FOR AND ON BEHALF OF THE COMPANY</p>
                        <p style='font-size: 9pt; color: #64748b; margin: 0;'>Authorised Signatory</p>
                        <p style='font-size: 9pt; color: #64748b; margin: 0;'>Date: ________________________</p>
                    </td>
                    <td style='width: 10%; border: none;'></td>
                    <td style='width: 45%; border: none; padding: 0;'>
                        <p style='font-weight: bold; border-bottom: 1px solid #334155; margin-bottom: 5px; padding-bottom: 60px;'></p>
                        <p style='font-size: 10pt; font-weight: bold; margin: 0;'>FOR THE {$typeLabel}</p>
                        <p style='font-size: 9pt; color: #64748b; margin: 0;'>Full Name: {$fullName}</p>
                        <p style='font-size: 9pt; color: #64748b; margin: 0;'>Date: ________________________</p>
                    </td>
                </tr>
            </table>

            <br/><br/><br/><br/>
            <hr style='border: none; border-top: 1px dashed #cbd5e1;'/>
            <p style='font-size: 8pt; color: #94a3b8; text-align: center; margin-top: 10px;'>
                This contract was generated by MyLoan's investment management system.<br/>
                Ref: {$investment->contract_ref}  |  Type: {$typeLabel}  |  Rate: {$ratePct}/month  |  Generated: {$investment->created_at->toDateTimeString()}
            </p>
        </body>
        </html>";
    }
}
