<?php
require 'config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Require login
if (!isset($_SESSION['uid'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['uid'];

// Categories
$categories = ['Fiction', 'Non-Fiction', 'Science', 'Romance', 'Children', 'Comics'];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Browse Books</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { margin:0; padding:0; background:#f1f3f6; font-family:'Poppins',sans-serif; }
    .navbar { background:#2874f0; padding:0.7rem 2rem; position:sticky; top:0; z-index:1000; width:100%; }
    .navbar-brand { color:#fff; font-weight:700; font-size:1.5rem; }
    .navbar-brand span { color:#ffe500; }
    .nav-link { color:#fff!important; font-weight:500; }
    .nav-link:hover { text-decoration:underline; }
    .filter-bar { background:#fff; padding:1rem; border-radius:8px; margin-bottom:1.5rem; box-shadow:0 2px 6px rgba(0,0,0,0.1); text-align:center; }
    .category-card { display:inline-block; padding:0.6rem 1rem; margin:0.2rem; background:#fff; border-radius:50px; font-weight:600; cursor:pointer; transition:0.2s; border:1px solid transparent; }
    .category-card.active, .category-card:hover { transform:scale(1.05); background:#e8f0fe; border:1px solid #2874f0; }
    .book-card { cursor:pointer; transition:transform 0.2s; background:#fff; border:none; border-radius:8px; }
    .book-card:hover { transform:scale(1.04); box-shadow:0 4px 12px rgba(0,0,0,0.15); }
    .book-img { height:200px; object-fit:contain; background:#f9f9f9; cursor:pointer; }
    .books-container { padding:2rem 0; }
    .btn-buy { background:#ff6f00; border:none; color:#fff; font-weight:600; width:100%; height:42px; }
    .btn-buy:hover { background:#e65100; }
    .btn-cart { background:#2874f0; border:none; color:#fff; font-weight:600; width:100%; height:42px; }
    .btn-cart:hover { background:#0b60d1; }
    .btn-group-custom { display:flex; gap:10px; margin-top:8px; }
  </style>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php">Book<span>Store</span></a>
    <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
      <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
      <li class="nav-item"><a class="nav-link active" href="browse_books.php">Browse</a></li>
      <li class="nav-item"><a class="nav-link" href="my_orders.php">My Orders</a></li>
      <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
    </ul>
  </div>
</nav>

<!-- Filter Bar -->
<div class="container mt-3 filter-bar">
  <div id="categoryContainer">
    <span class="category-card active" data-category="All">All</span>
    <?php foreach($categories as $cat): ?>
      <span class="category-card" data-category="<?=htmlspecialchars($cat)?>"><?=htmlspecialchars($cat)?></span>
    <?php endforeach; ?>
  </div>
  <div class="mt-2">
    <input type="text" id="searchInput" placeholder="Search books..." class="form-control d-inline-block w-50">
    <select id="sortSelect" class="form-select d-inline-block w-auto ms-2">
      <option value="latest">Sort by Latest</option>
      <option value="low">Price: Low to High</option>
      <option value="high">Price: High to Low</option>
    </select>
  </div>
</div>

<!-- Books Container -->
<div class="container books-container">
  <div class="row g-4" id="bookContainer">
    <!-- Books will be loaded via AJAX -->
  </div>
</div>

<!-- Book Image Modal with Close Button -->
<div class="modal fade" id="bookModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0">
        <img src="" id="modalBookImg" class="w-100" style="object-fit:contain;">
      </div>
    </div>
  </div>
</div>

<script>
let selectedCategory = 'All';
let searchQuery = '';
let selectedSort = 'latest';

function fetchBooks() {
  $('#bookContainer').fadeTo(200, 0.3, function() {
    $.get('fetch_books.php', {
      category: selectedCategory,
      q: searchQuery,
      sort: selectedSort
    }, function(data) {
      $('#bookContainer').html(data).fadeTo(400, 1);
      // Make loaded images clickable
      $('.book-img').addClass('book-img-clickable');
    });
  });
}

$(document).ready(function() {
  fetchBooks();

  // Category click
  $(document).on('click', '.category-card', function() {
    $('.category-card').removeClass('active');
    $(this).addClass('active');
    selectedCategory = $(this).data('category');
    fetchBooks();
  });

  // Search
  $('#searchInput').on('keyup', function() {
    searchQuery = $(this).val();
    fetchBooks();
  });

  // Sort
  $('#sortSelect').change(function() {
    selectedSort = $(this).val();
    fetchBooks();
  });

  // Open modal with clicked image
  $(document).on('click', '.book-img-clickable', function() {
    let src = $(this).attr('src');
    $('#modalBookImg').attr('src', src);
    let modal = new bootstrap.Modal(document.getElementById('bookModal'));
    modal.show();
  });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
