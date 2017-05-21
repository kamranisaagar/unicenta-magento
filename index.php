	<?php
		require_once('db.php');

		//Fetching Active Order List from Magento
		$query = "SELECT increment_id, CONCAT(customer_firstname,\" \",customer_lastname) AS name, TRUNCATE(base_grand_total,2) as amount FROM sales_flat_order so WHERE so.status=\"pending\" or so.status=\"processing\" ORDER BY created_at DESC";

		$result = $link2->query($query) or die("Error in the consult.." . mysqli_error($link2));
		?>

		<html lang="en">
		  <head>
		    <meta charset="utf-8">
		    <meta http-equiv="X-UA-Compatible" content="IE=edge">
		    <meta name="viewport" content="width=device-width, initial-scale=1">
		    <title>Get Orders</title>
			
		    <!-- Bootstrap CSS -->
		    <link href="css/bootstrap.min.css" rel="stylesheet" media="all">
			<!-- Ends Here -->
		  </head>
		  <body>
		<!-- Bootstrap table with filter -->
			<div class="container">
				<div class="row">
				<h3>Get Orders</h3><br>
				<?php if (isset($_GET['success']))
					{  
				 	echo "<div class=\"alert alert-success\"><b>Order # {$_GET['orderid']} has been written with ticket # {$_GET['ticketid']}  </b></div>";
					} 
				 ?>
				<table class="table">
					<thead><tr>
						<th>Order Id</th>
						<th>Customer Name</th>
						<th>Amount</th>
						<th></th>
					</tr></thead>

					<tbody>
					<?php
					//Table Data
					while ($row = mysqli_fetch_assoc($result))
					{
					echo "
					<tr>
					<td id=\"orderid\" >{$row['increment_id']}</td>
					<td id=\"cust_name\" >{$row['name']}</td>
					<td id=\"amount\" >{$row['amount']}</td>
					<td id=\"process\" ><a href=\"write_order.php?Submit=true&txtorderid={$row['increment_id']}&txtpayment={$row['amount']}\">Process Order</a></td>
					</tr>";
					}
		            ?> 
				    </tbody>
				</table>
			</div>
		</div>
		  </body>
		</html>