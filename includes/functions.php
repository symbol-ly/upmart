<?php
/* ---------------------------------------------------------
   UP MIN MARKETPLACE - BACKEND CORE FUNCTIONS
   ---------------------------------------------------------
*/

// --- USER ACTIONS ---

/**
 * Validates if the email is a legitimate UP Mindanao email
 */
function validate_up_email($email) {
    return str_ends_with($email, '@up.edu.ph');
}

/**
 * Updates basic user info (Name and Role)
 */
function user_update_profile($conn, $user_id, $full_name, $role) {
    $stmt = $conn->prepare("UPDATE Users SET full_name = ?, user_role = ? WHERE user_id = ?");
    $stmt->bind_param("ssi", $full_name, $role, $user_id);
    return $stmt->execute();
}


// --- PRODUCT ACTIONS ---

/**
 * Adds a new item to the marketplace
 */
function product_create_listing($conn, $seller_id, $cat_id, $title, $desc, $price, $cond) {
    $stmt = $conn->prepare("INSERT INTO Products (seller_id, category_id, title, description, price, item_condition) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissds", $seller_id, $cat_id, $title, $desc, $price, $cond);
    return $stmt->execute();
}

// --- TRANSACTION ACTIONS ---

/**
 * Logs when a buyer is interested in a product
 */
function inquiry_log_click($conn, $buyer_id, $product_id) {
    $stmt = $conn->prepare("INSERT INTO Transactions (buyer_id, product_id, message_link_clicked) VALUES (?, ?, 1)");
    return $stmt->execute();
}

// --- UTILITY ACTIONS ---


function format_currency($amount) {
    return "₱" . number_format($amount, 2);
}

function product_add_image($conn, $seller_id, $cat_id, $title, $desc, $price, $cond, $image_path) {
    $stmt = $conn->prepare("INSERT INTO Products (seller_id, category_id, title, description, price, item_condition) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissds", $seller_id, $cat_id, $title, $desc, $price, $cond);
    
    if ($stmt->execute()) {
        $product_id = $conn->insert_id; 
        
        $stmt_media = $conn->prepare("INSERT INTO Media (product_id, image_path) VALUES (?, ?)");
        $stmt_media->bind_param("is", $product_id, $image_path);
        return $stmt_media->execute();
    }
    return false;
}

/**
 * Updates an existing product's details
 */
function product_update_listing($conn, $product_id, $seller_id, $cat_id, $title, $desc, $price, $cond, $status) {
    // We include seller_id to ensure only the owner can edit their own product
    $stmt = $conn->prepare("UPDATE Products SET category_id = ?, title = ?, description = ?, price = ?, item_condition = ?, status = ? WHERE product_id = ? AND seller_id = ?");
    
    // "issds sii" -> int, string, string, double, string, string, int, int
    $stmt->bind_param("issdssii", $cat_id, $title, $desc, $price, $cond, $status, $product_id, $seller_id);
    
    return $stmt->execute();
}

/**
 * Deletes a product, its database media records, and the physical image file
 */
function product_delete($conn, $product_id, $seller_id) {
    // 1. Get the image path first so we can delete the physical file
    $stmt_img = $conn->prepare("SELECT image_path FROM Media WHERE product_id = ?");
    $stmt_img->bind_param("i", $product_id);
    $stmt_img->execute();
    $result = $stmt_img->get_result();
    
    while ($row = $result->fetch_assoc()) {
        if (file_exists($row['image_path'])) {
            unlink($row['image_path']); // This deletes the actual file from the folder
        }
    }

    // 2. Delete the product (Database cascades will handle Media/Transaction rows if set up, 
    // but we'll do it manually if not)
    $stmt = $conn->prepare("DELETE FROM Products WHERE product_id = ? AND seller_id = ?");
    $stmt->bind_param("ii", $product_id, $seller_id);
    
    return $stmt->execute();
}

/**
 * Quickly updates only the status (Available, Sold, Reserved)
 */
function product_update_status($conn, $product_id, $seller_id, $new_status) {
    $stmt = $conn->prepare("UPDATE Products SET status = ? WHERE product_id = ? AND seller_id = ?");
    $stmt->bind_param("sii", $new_status, $product_id, $seller_id);
    return $stmt->execute();
}

/**
 * Fetches all available products from all suppliers, newest first
 * Includes Seller Name and Category Name via SQL Joins
 */
function product_display($conn) {
    $sql = "SELECT p.*, u.full_name AS seller_name, c.category_name 
            FROM Products p
            JOIN Users u ON p.seller_id = u.user_id
            JOIN Categories c ON p.category_id = c.category_id
            WHERE p.status = 'Available'
            ORDER BY p.product_id DESC"; // Latest ID = Latest Post
            
    $result = $conn->query($sql);
    
    $products = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
    return $products;
}

/**
 * Fetches products with optional search and category filters
 */
function product_get_filtered($conn, $search = '', $category_id = '') {
    $sql = "SELECT p.*, u.full_name AS seller_name, c.category_name 
            FROM Products p
            JOIN Users u ON p.seller_id = u.user_id
            JOIN Categories c ON p.category_id = c.category_id
            WHERE p.status = 'Available'";

    // Add search filter if keyword exists
    if (!empty($search)) {
        $search = $conn->real_escape_string($search);
        $sql .= " AND (p.title LIKE '%$search%' OR p.description LIKE '%$search%')";
    }

    // Add category filter if selected
    if (!empty($category_id)) {
        $category_id = (int)$category_id;
        $sql .= " AND p.category_id = $category_id";
    }

    $sql .= " ORDER BY p.product_id DESC";
    
    $result = $conn->query($sql);
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    return $products;
}

?>

