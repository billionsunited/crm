<?php

function amountInWords(float $number)
{
    $no = floor($number);
    $point = round($number - $no, 2) * 100;
    $hundred = null;
    $digits_1 = strlen((string) $no);
    $i = 0;
    $str = array();
    $words = array(
        '0' => '',
        '1' => 'one',
        '2' => 'two',
        '3' => 'three',
        '4' => 'four',
        '5' => 'five',
        '6' => 'six',
        '7' => 'seven',
        '8' => 'eight',
        '9' => 'nine',
        '10' => 'ten',
        '11' => 'eleven',
        '12' => 'twelve',
        '13' => 'thirteen',
        '14' => 'fourteen',
        '15' => 'fifteen',
        '16' => 'sixteen',
        '17' => 'seventeen',
        '18' => 'eighteen',
        '19' => 'nineteen',
        '20' => 'twenty',
        '30' => 'thirty',
        '40' => 'forty',
        '50' => 'fifty',
        '60' => 'sixty',
        '70' => 'seventy',
        '80' => 'eighty',
        '90' => 'ninety'
    );
    $digits = array('', 'hundred', 'thousand', 'lakh', 'crore');

    while ($i < $digits_1) {
        $divider = ($i == 2) ? 10 : 100;
        $number = floor($no % $divider);
        $no = floor($no / $divider);
        $i += ($divider == 10) ? 1 : 2;
        if ($number) {
            $plural = (($counter = count($str)) && $number > 9) ? '' : null;
            $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
            $str[] = ($number < 21) ? $words[$number] . " " . $digits[$counter] . $plural . " " . $hundred
                : $words[floor($number / 10) * 10] . " " . $words[$number % 10] . " " . $digits[$counter] . $plural . " " . $hundred;
        } else
            $str[] = null;
    }

    $str = array_reverse($str);
    $result = implode('', $str);
    $result = preg_replace('/ and $/i', '', trim(str_replace('  ', ' ', $result)));

    $points = '';
    if ($point) {
        $tens = floor($point / 10) * 10;
        $ones = $point % 10;
        $pointStr = ($point < 21) ? $words[$point] : $words[$tens] . " " . $words[$ones];
        $points = " and " . $pointStr . " paise";
    }

    if (empty($result)) {
        $result = "zero";
    }

    return ucfirst(strtolower(trim($result . " rupees" . $points . " only")));
}

echo "8260: " . amountInWords(8260.00) . "\n";
echo "8200: " . amountInWords(8200.00) . "\n";
echo "100: " . amountInWords(100.00) . "\n";
echo "120: " . amountInWords(120.00) . "\n";
echo "1000: " . amountInWords(1000.00) . "\n";
