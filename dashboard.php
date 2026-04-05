<?php
// Dashboard with contact management (with picture & email): dashboard.php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$search_term = '';

// Handle contact addition with picture upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_contact'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $picture_path = null;
    
    // Handle picture upload
    if (isset($_FILES['picture']) && $_FILES['picture']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = $_FILES['picture']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            // Create uploads directory if it doesn't exist
            if (!is_dir('uploads')) {
                mkdir('uploads', 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['picture']['name'], PATHINFO_EXTENSION);
            $unique_filename = uniqid() . '_' . time() . '.' . $file_extension;
            $upload_path = 'uploads/' . $unique_filename;
            
            if (move_uploaded_file($_FILES['picture']['tmp_name'], $upload_path)) {
                $picture_path = $upload_path;
            } else {
                $message = '<div class="alert alert-danger">Failed to upload picture</div>';
            }
        } else {
            $message = '<div class="alert alert-danger">Invalid file type. Only JPG, PNG, GIF, WEBP allowed.</div>';
        }
    }
    
    if (empty($name) || empty($phone)) {
        $message = '<div class="alert alert-danger">Please fill at least Name and Phone Number</div>';
    } else {
        $stmt = $conn->prepare("INSERT INTO contacts (user_id, name, email, phone, picture) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $user_id, $name, $email, $phone, $picture_path);
        if ($stmt->execute()) {
            $message = '<div class="alert alert-success">Contact added successfully!</div>';
        } else {
            $message = '<div class="alert alert-danger">Failed to add contact: ' . $conn->error . '</div>';
        }
        $stmt->close();
    }
}

// Handle contact deletion
if (isset($_GET['delete'])) {
    $contact_id = intval($_GET['delete']);
    
    // Get picture path before deleting
    $stmt = $conn->prepare("SELECT picture FROM contacts WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $contact_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        if ($row['picture'] && file_exists($row['picture'])) {
            unlink($row['picture']);
        }
    }
    $stmt->close();
    
    $stmt = $conn->prepare("DELETE FROM contacts WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $contact_id, $user_id);
    if ($stmt->execute()) {
        $message = '<div class="alert alert-success">Contact deleted successfully!</div>';
    }
    $stmt->close();
    
    // Redirect to avoid re-deletion on page refresh
    header("Location: dashboard.php");
    exit();
}

// Handle contact update - FIXED
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_contact'])) {
    $contact_id = intval($_POST['contact_id']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    
    if (empty($name) || empty($phone)) {
        $message = '<div class="alert alert-danger">Name and Phone are required!</div>';
    } else {
        $stmt = $conn->prepare("UPDATE contacts SET name = ?, email = ?, phone = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("sssii", $name, $email, $phone, $contact_id, $user_id);
        if ($stmt->execute()) {
            $message = '<div class="alert alert-success">Contact updated successfully!</div>';
        } else {
            $message = '<div class="alert alert-danger">Failed to update contact: ' . $conn->error . '</div>';
        }
        $stmt->close();
    }
}

// Fetch all contacts for the user
$contacts = [];
$stmt = $conn->prepare("SELECT id, name, email, phone, picture, created_at FROM contacts WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $contacts[] = $row;
}
$stmt->close();

// Handle search - reorder contacts based on search term (but show all contacts)
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search_term = trim($_GET['search']);
    $search_lower = strtolower($search_term);
    
    // Sort contacts by relevance to search term
    usort($contacts, function($a, $b) use ($search_lower) {
        $a_name = strtolower($a['name']);
        $b_name = strtolower($b['name']);
        $a_email = strtolower($a['email']);
        $b_email = strtolower($b['email']);
        $a_phone = strtolower($a['phone']);
        $b_phone = strtolower($b['phone']);
        
        // Calculate relevance score for contact A
        $a_score = 0;
        if (strpos($a_name, $search_lower) !== false) $a_score += 3;
        if (strpos($a_email, $search_lower) !== false) $a_score += 2;
        if (strpos($a_phone, $search_lower) !== false) $a_score += 1;
        
        // Calculate relevance score for contact B
        $b_score = 0;
        if (strpos($b_name, $search_lower) !== false) $b_score += 3;
        if (strpos($b_email, $search_lower) !== false) $b_score += 2;
        if (strpos($b_phone, $search_lower) !== false) $b_score += 1;
        
        // Sort by score (higher score first), then by name
        if ($a_score != $b_score) {
            return $b_score - $a_score;
        }
        return strcmp($a_name, $b_name);
    });
} else {
    // Default ordering by name
    usort($contacts, function($a, $b) {
        return strcmp($a['name'], $b['name']);
    });
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Contact Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
        /* Basic navbar styles */
        .navbar {
            background: #2c3e50;
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        .navbar a {
            color: white;
            text-decoration: none;
            margin-left: 1rem;
        }
        .navbar a:hover {
            text-decoration: underline;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        .add-contact {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
        }
        .form-row {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        .form-group {
            flex: 1;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }
        .form-group input {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button[type="submit"] {
            background: #3498db;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
        }
        button[type="submit"]:hover {
            background: #2980b9;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 2rem;
            border-radius: 8px;
            width: 50%;
            max-width: 500px;
        }
        .close {
            float: right;
            font-size: 1.5rem;
            cursor: pointer;
        }
        /* Search bar styles */
        .search-container {
            margin-bottom: 2rem;
            background: white;
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .search-form {
            display: flex;
            gap: 1rem;
            align-items: flex-end;
        }
        .search-form .form-group {
            flex: 1;
        }
        .search-form button {
            margin-bottom: 0;
            height: 42px;
        }
        .clear-search {
            background: #6c757d;
        }
        .clear-search:hover {
            background: #5a6268;
        }
        .search-info {
            margin-top: 0.5rem;
            color: #6c757d;
            font-size: 0.9rem;
        }
        .relevance-badge {
            display: inline-block;
            background: #e8f4f8;
            color: #3498db;
            font-size: 0.75rem;
            padding: 0.2rem 0.5rem;
            border-radius: 12px;
            margin-left: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1> Contact Manager</h1>
        <div>
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
            <a href="logout.php">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <?php echo $message; ?>
        

        
        <div class="add-contact">
            <h2> Add New Contact</h2>
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="name" placeholder="Enter full name" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" placeholder="Enter email address">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Phone Number *</label>
                        <input type="tel" name="phone" placeholder="Enter phone number" required>
                    </div>
                    <div class="form-group">
                        <label>Profile Picture</label>
                        <input type="file" name="picture" accept="image/jpeg,image/png,image/gif,image/webp">
                    </div>
                </div>
                <button type="submit" name="add_contact">Save Contact</button>
            </form>
        </div>
        
        <div class="contacts-list">
            <h2> Your Contacts</h2>

                    <!-- Search Section - Only reorders contacts -->
        <div class="search-container">
            <form method="GET" action="" class="search-form">
                <div class="form-group">
                    <label> Search to Reorder Contacts</label>
                    <input type="text" name="search" placeholder="Search by name, email, or phone to bring matching contacts to the top..." 
                           value="<?php echo htmlspecialchars($search_term); ?>" 
                           class="form-control">
                    <small class="form-text text-muted"></small>
                </div>
                <button type="submit" class="btn btn-primary">Reorder</button>
                <?php if (!empty($search_term)): ?>
                    <a href="dashboard.php" class="btn clear-search">Show All (Reset Order)</a>
                <?php endif; ?>
            </form>
            <?php if (!empty($search_term)): ?>
                <div class="search-info">
                     Reordered by relevance to: "<strong><?php echo htmlspecialchars($search_term); ?></strong>" 
                    (<?php echo count($contacts); ?> total contacts)
                </div>
            <?php endif; ?>
        </div>
            <?php if (count($contacts) > 0): ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Picture</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone Number</th>
                            <th>Added On</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contacts as $index => $contact): ?>
                            <tr>
                                <td>
                                    <?php if ($contact['picture'] && file_exists($contact['picture'])): ?>
                                        <img src="<?php echo htmlspecialchars($contact['picture']); ?>" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;" alt="Profile">
                                    <?php else: ?>
                                        <div style="width: 50px; height: 50px; border-radius: 50%; background: #ccc; display: inline-block;"></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($contact['name']); ?>
                                    <?php if (!empty($search_term)): 
                                        $search_lower = strtolower($search_term);
                                        $name_lower = strtolower($contact['name']);
                                        $email_lower = strtolower($contact['email']);
                                        $phone_lower = strtolower($contact['phone']);
                                        
                                        if (strpos($name_lower, $search_lower) !== false || 
                                            strpos($email_lower, $search_lower) !== false || 
                                            strpos($phone_lower, $search_lower) !== false):
                                    ?>
                                        <span class="relevance-badge">✓ Match</span>
                                    <?php endif; endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($contact['email'] ?: '—'); ?></td>
                                <td><?php echo htmlspecialchars($contact['phone']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($contact['created_at'])); ?></td>
                                <td>
                                    <button class="btn btn-primary btn-sm" onclick='openEditModal(<?php echo json_encode($contact); ?>)'>Edit</button>
                                    <a href="?delete=<?php echo $contact['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this contact?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-info">
                     No contacts yet. Add your first contact above!
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Edit Contact Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h2> Edit Contact</h2>
            <form method="POST" action="">
                <input type="hidden" name="contact_id" id="edit_contact_id">
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="name" id="edit_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" id="edit_email" class="form-control">
                </div>
                <div class="form-group">
                    <label>Phone Number *</label>
                    <input type="tel" name="phone" id="edit_phone" class="form-control" required>
                </div>
                <button type="submit" name="update_contact" class="btn btn-success">Update Contact</button>
            </form>
        </div>
    </div>
    
    <script>
        // FIXED: JavaScript functions properly defined
        function openEditModal(contact) {
            console.log('Opening modal for contact:', contact); // Debug log
            document.getElementById('edit_contact_id').value = contact.id;
            document.getElementById('edit_name').value = contact.name;
            document.getElementById('edit_email').value = contact.email || '';
            document.getElementById('edit_phone').value = contact.phone;
            document.getElementById('editModal').style.display = 'block';
        }
        
        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target == document.getElementById('editModal')) {
                closeEditModal();
            }
        }
    </script>
</body>
</html>