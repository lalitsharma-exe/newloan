<?php
$file = 'resources/views/admin/loans/agreement-pdf.blade.php';
$content = file_get_contents($file);

// 1. Borrower Address
$content = preg_replace(
    '/(<strong>Residential Address:<\/strong> ).*?(<br>)/',
    '$1{{ implode(\', \', array_filter([$loan->application?->village, $loan->application?->town, $loan->application?->district])) ?: ($loan->user->address ?? \'_______________________________________________\') }}$2',
    $content
);

// 2. T&C Header
$content = str_replace('<h2>TERMS AND CONDITIONS</h2>', '<h2>3. TERMS AND CONDITIONS</h2>', $content);

// 3. Renumber clauses 3 to 25 to 3.1 to 3.23
for ($i = 3; $i <= 25; $i++) {
    $newMatch = '3.' . ($i - 2);
    $content = preg_replace('/<div class="clause"><h3>' . $i . '\. /', '<div class="clause"><h3>' . $newMatch . '. ', $content);
}

// 4. Update section 17 text
$oldSec17 = '<p>The Borrower acknowledges that loan agreements may be signed electronically, electronic records shall be valid proof of the agreement, and a One-Time Password (OTP) sent to the Borrower\'s phone number constitutes a valid digital signature.</p>';
$newSec17 = '<p>By proceeding, you consent to the use of electronic records and agree that all loan documents may be issued and stored digitally. You authorize the use of a digital signature, including signing on a device using your finger, stylus, or mouse. You also agree that a One-Time Password (OTP) sent to your registered mobile number may be used as your electronic signature. You acknowledge that such electronic signatures are legally binding and equivalent to a handwritten signature. Once the loan is approved it can be disbursed to the specified payment method.</p>';
$content = str_replace($oldSec17, $newSec17, $content);

// 5. Signatures Header
$content = str_replace('<h2>26. SIGNATURES</h2>', '<h2>4. SIGNATURES</h2>', $content);

// 6. Real signature & Managing Director
$content = str_replace('Signature — Tjale Maila (Director)', 'Signature — Tjale Maila (Managing Director)', $content);
$content = str_replace('Authorised Representative: <strong>Tjale Maila</strong>', 'Authorised Representative: <strong>Tjale Maila (Managing Director)</strong>', $content);

$svgPattern = '/<svg viewBox=.*?<\/svg>/s';
$realSignature = '<div style="font-family:\'Brush Script MT\', cursive; font-size:32px; color:#1a5c2e; transform:rotate(-5deg)">Tjale Maila</div>';
$content = preg_replace($svgPattern, $realSignature, $content);

// 7. Add page numbering css inside <style>
$css = <<<'CSS'
  @page {
    margin: 30px;
    @bottom-right {
      content: "Page " counter(page) " of " counter(pages);
      font-size: 10px;
      color: #64748b;
    }
  }
CSS;
$content = str_replace('</style>', $css . "\n</style>", $content);

file_put_contents($file, $content);
echo "Done\n";
