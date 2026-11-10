<?php

declare(strict_types=1);

function getTransactionFiles(string $dirPath): array{ //استخراج كل الملفات من المجلد
    $files =[];
     foreach(scandir($dirPath) as $file){
        if(is_dir($file))
            continue;
       $files[] = $dirPath .  $file;
     } 
     return $files;
} 
function getTransactions(string $fileName, ?callable $transactionhandler= null): array{ // محاولة فتح الملف و قراءة محتوياته
  if(!file_exists($fileName)){
    trigger_error('File'.$fileName."does not exit." ,E_USER_ERROR);
  }

  $file = fopen($fileName, 'r');

  $transactions = [];

  while(($transaction = fgetcsv($file) ) !== false){
    if($transactionhandler !== null){ //to make getTransactions function flixible enough , doesn't depend on individual transaction (suitable for banks' files and others )
      $transaction = $transactionhandler($transaction);
    }
       $transactions[] = extractTransaction($transaction);
  } 
  return $transactions;
}

function extractTransaction(array $transactionRow): array{
  [$date, $checkNumber,$description,$amount]= $transactionRow;
  //amount بدو يتجاهل الفاصلة واشارة الدولار ليتمكن من عمل ععمليات حسابية عليها 
  $amount = (float)(str_replace([',','$'], '',(string)$amount));
 
  return [
      $date,
    $checkNumber,
    $description,
     $amount
  ];
}

function calculateTotals(array $transactions): array{
  $total = ['netTotal' => 0, 'totalIncome'=> 0, 'totalExpense'=> 0];

  foreach($transactions as $transaction){
    $total['netTotal'] += $transaction[3];
    if($transaction[3] >= 0){
      $total['totalIncome'] += $transaction[3];
    }
    else{
      $total['totalExpense'] += $transaction[3];
    }
  }
  return $total;
}