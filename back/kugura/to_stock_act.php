<?php
session_start();
include('../../inc/connection.php'); 
include('../clude/funcs.php');
$user_id = $_SESSION['user_id'];
page_restrict();

mysqli_begin_transaction($connection); // Begin the transaction

try {
    $item_id = $_REQUEST['id'];
    
    // Fetch session details (PO and GRN)
    $get_details = mysqli_query($connection, "SELECT * FROM kp_thesion WHERE user_id='$user_id'") or die("Could not get the user permissions");
    $row = mysqli_fetch_assoc($get_details);
    $po_id = $row['po_id'];  
    $grn_id = $row['grn_id'];

    // Fetch item details for the given GRN and item
    $get_po_details = mysqli_query($connection, "SELECT * FROM kp_grn_items WHERE grn_id='$grn_id' AND item_id='$item_id'") or die("Could not get the purchase details");
    $row = mysqli_fetch_assoc($get_po_details);
    $rec_qty = $row['qty'];

    // Fetch the store IDs where the item is stored
    $get_details = mysqli_query($connection, "SELECT store_id FROM kp_items WHERE item_id='$item_id'") or die("Could not get the store details");
    $row = mysqli_fetch_assoc($get_details);
    $store_id = str_replace([' ', '.00'], '', $row['store_id']);
    $arr = explode(',', $store_id);
    $arry = array_unique($arr);

    $sum = 0;

    // Loop through each store and calculate the sum of quantities entered for each store
    foreach ($arry as $c) {
        $input = "qty" . $c; // Input name format for each store's qty
        
        // Capture quantity from form input for this store
        $qty = isset($_REQUEST[$input]) ? $_REQUEST[$input] : 0;

        // Convert quantity to integer, ensure it’s valid
        $qty = intval($qty); 
        
        if ($qty > 0) {
            $sum += $qty;
        }
    }

    // Check if the sum of all store quantities matches the received quantity
    if ($sum == 0) {
        throw new Exception("Error: The total received quantity is zero.");
    } elseif ($sum != $rec_qty) {
        throw new Exception("Error: The total received quantity does not match the expected quantity.");
    } else {
        // Now proceed to update store-specific quantities and stock tracking
        foreach ($arry as $i) {
            $input = "qty" . $i; // Input name for store quantity
            $qty = isset($_REQUEST[$input]) ? $_REQUEST[$input] : 0; // Capture the input qty

            // Convert quantity to integer to prevent invalid entries
            $colqty = intval($qty);

            if ($colqty > 0) {
                // Insert the stock movement for this store
                $update_track = mysqli_query($connection, "INSERT INTO kp_stock_store_track(store_id, grn_id, item_id, po_id, user_id, qty, status, day) VALUES ('$i', '$grn_id', '$item_id', '$po_id', '$user_id', '$colqty', 'IN', CURRENT_DATE)");
                if (!$update_track) {
                    throw new Exception("Failed to update stock tracking for store ID $i.");
                }

                // Update the specific store quantity in kp_items for this store
                $column = "store" . $i . "_qty";
                $edit_qty = "UPDATE kp_items SET $column = $column + '$qty' WHERE item_id = '$item_id'";
                $edit_qty_r = mysqli_query($connection, $edit_qty);
                if (!$edit_qty_r) {
                    throw new Exception("Failed to update store quantity for store ID $i.");
                }
            }
        }

        // Calculate the total stock across all stores for this item
        $total_qty = all_stores_total($item_id);

        // Update the total quantity for the item in kp_items
        $edit_item = "UPDATE kp_items SET qty = '$total_qty' WHERE item_id = '$item_id'";
        $edit_item_r = mysqli_query($connection, $edit_item);
        if (!$edit_item_r) {
            throw new Exception("Failed to update total stock for item.");
        }

        // Update the s2s status in kp_po_items and kp_grn_items
        $update_po_item = mysqli_query($connection, "UPDATE kp_po_items SET s2s = IF(rec_qty = qty, 1, s2s) WHERE po_id = '$po_id' AND item_id = '$item_id'");
        $update_grn_item = mysqli_query($connection, "UPDATE kp_grn_items SET s2s = 1 WHERE grn_id = '$grn_id' AND item_id = '$item_id'");
        if (!$update_po_item || !$update_grn_item) {
            throw new Exception("Failed to update purchase order or GRN item status.");
        }

        // Commit the transaction after successful updates
        mysqli_commit($connection);

        ?>
        <script type="text/javascript">
            window.location.reload();
        </script>
        <?php
    }
} catch (Exception $e) {
    mysqli_rollback($connection);
    echo "Transaction failed: " . $e->getMessage();
}

?>
