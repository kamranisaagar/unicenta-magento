<?php
require_once('db.php');

if (isset($_GET['Submit'])) {
	$orderid = $_GET['txtorderid'];
	$payment=$_GET['txtpayment'];
	$receiptid="ON-".$orderid;
	$paymentid="PAY-".$orderid;
	$paymentamount=$payment;
	$money=getMoneyid();
	$ticketid=getTicketId();
	$products=getProducts($orderid);
	$taxlineid="TAX-".$orderid;
	$untaxed_am=$payment/1.1;
	$total_tax=$payment-$untaxed_am;
	
	writeReceipt($receiptid,$money);
	writeTicket($receiptid, $ticketid);
	writeTicketLines($receiptid,$products);
	writePayment($paymentid,$receiptid,$paymentamount);
	writeTaxLines($taxlineid,$untaxed_am,$total_tax,$receiptid);
	writeTicketsNum($ticketid);
	$header_var="ticketid={$ticketid}&orderid={$orderid}&success=true";
	header('Location: index.php?'.$header_var);
}

function writeReceipt($receiptid,$money){
	global $link;
	$xml="<?xml version=\\\"1.0\\\" encoding=\\\"UTF-8\\\" standalone=\\\"no\\\"?><!DOCTYPE properties SYSTEM \\\"http://java.sun.com/dtd/properties.dtd\\\"><properties><comment>TSG POS</comment></properties>";
//	echo $xml;
    $query_writeReceipt = "INSERT INTO receipts VALUES (\"$receiptid\",\"$money\",NOW(),\"$xml\",NULL)";
   $result_writeReceipt = $link->query($query_writeReceipt) or die("Error in the consult.." . mysqli_error($link));
    echo "Written in Receipts<br><br>";
}

function writeTicket($receiptid, $ticketid){
	global $link;

    $query_writeTicket = "INSERT INTO tickets VALUES (\"$receiptid\",\"0\",\"$ticketid\",\"1\",NULL,\"0\")";
    $result_writeTicket = $link->query($query_writeTicket) or die("Error in the consult.." . mysqli_error($link));
    echo "Written in Tickets<br><br>";
}

function writeTicketLines($receiptid,$products){
	global $link;
	$line=0;
	foreach($products as $row) {
    $attributes_xml="<?xml version=\\\"1.0\\\" encoding=\\\"UTF-8\\\" standalone=\\\"no\\\"?><!DOCTYPE properties SYSTEM \\\"http://java.sun.com/dtd/properties.dtd\\\"><properties><comment>TSG POS</comment><entry key=\\\"product.taxcategoryid\\\">001</entry><entry key=\\\"product.warranty\\\">false</entry><entry key=\\\"product.verpatrib\\\">false</entry><entry key=\\\"product.name\\\">{$row[1]}</entry><entry key=\\\"product.service\\\">false</entry><entry key=\\\"product.com\\\">false</entry><entry key=\\\"PROMO\\\">Online Order {$receiptid}</entry><entry key=\\\"product.texttip\\\">{$row[1]}</entry><entry key=\\\"product.categoryid\\\">001</entry><entry key=\\\"product.vprice\\\">false</entry><entry key=\\\"product.kitchen\\\">false</entry></properties>";
	
	
    $query_writeTicketLines = "INSERT INTO ticketlines VALUES (\"$receiptid\",\"$line\",\"$row[0]\",NULL,\"$row[2]\",\"$row[3]\",\"001\",\"$attributes_xml\",\"DELIVERED\",\"0\")";
    $result_writeTicketLines = $link->query($query_writeTicketLines) or die("Error in the consult.." . mysqli_error($link));
	$line++;
	}
    echo "Written in TicketLines<br><br>";
}

function writePayment($paymentid,$receiptid,$paymentamount){
	global $link;

    $query_writePayment = "INSERT INTO payments VALUES (\"$paymentid\",\"$receiptid\",\"cheque\",\"$paymentamount\",NULL,\"OK\",NULL)";
    $result_writePayment = $link->query($query_writePayment) or die("Error in the consult.." . mysqli_error($link));
    echo "Written in Payments<br><br>";
}

function writeTaxLines($taxlineid,$untaxed_am,$total_tax,$receiptid)
{
	global $link;

    $query_writeTaxLine = "INSERT INTO taxlines VALUES (\"$taxlineid\",\"$receiptid\",\"001\",\"$untaxed_am\",\"$total_tax\")";
    $result_writeTaxLine = $link->query($query_writeTaxLine) or die("Error in the consult.." . mysqli_error($link));
    echo "Written in TaxLines<br><br>";
}

function writeTicketsNum($ticketid)
{
	global $link;

    $query_writeTicketsNum = "Update ticketsnum set ID=\"$ticketid\"";
    $result_writeTicketsNum = $link->query($query_writeTicketsNum) or die("Error in the consult.." . mysqli_error($link));
    echo "Written in TicketsNum<br><br>";
}

function getMoneyId() {
	global $link;

    $query_moneyid = "select money from closedcash where hostsequence = (select max(hostsequence) from closedcash) and dateend is null";
    $result_moneyid = $link->query($query_moneyid) or die("Error in the consult.." . mysqli_error($link));
   
    while ($row_moneyid = mysqli_fetch_assoc($result_moneyid)) {
        $moneyid = $row_moneyid['money'];
    }
    return $moneyid;
}

function getTicketId() {
	global $link;

    $query_ticketid = "SELECT ID+1 AS ticketid FROM ticketsnum";
    $result_ticketid = $link->query($query_ticketid) or die("Error in the consult.." . mysqli_error($link));
   
    while ($row_ticketid = mysqli_fetch_assoc($result_ticketid)) {
        $ticketid = $row_ticketid['ticketid'];
    }
    return $ticketid;
}

function getProducts($orderid) {
    global $link2;

    $query_products = "SELECT si.sku AS sku, si.name AS product_name, TRUNCATE(qty_ordered,0) as qty, TRUNCATE(price_incl_tax,2) AS price
			FROM sales_flat_order so
			JOIN sales_flat_order_item si ON so.entity_id=si.order_id AND so.increment_id=\"$orderid\"";
    
    $result_products = $link2->query($query_products) or die("Error in the consult.." . mysqli_error($link2));
    $products = array();

    while ($row_products = mysqli_fetch_assoc($result_products)) {
        $products[$row_products['sku']][0] = getProductID($row_products['sku']);
		$products[$row_products['sku']][1] = str_ireplace("&","and",$row_products['product_name']);
		$products[$row_products['sku']][2] = $row_products['qty'];
		$products[$row_products['sku']][3] = $row_products['price']/1.1;
    }
    return $products;
}

function getProductID($sku) {
    global $link;

    $query_productid = "SELECT id FROM products WHERE CODE=\"$sku\"";
    $result_productid = $link->query($query_productid) or die("Error in the consult.." . mysqli_error($link));
	if(mysqli_num_rows($result_productid) == 0)
	{	
	 //This is a Random Variable Product Added on POS; incase your local POS system doesn't have product you sold online.
	 $productid="ON-MISC123";	
	}
    else 
	{
	while ($row_productid = mysqli_fetch_assoc($result_productid)) {
        
			$productid = $row_productid['id'];
		 }
    }
    return $productid;
}

?>