<?php
require 'config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Bookstore | Home</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { margin: 0; background-color: #f1f3f6; font-family: 'Poppins', sans-serif; }
    .navbar { background-color: #2874f0; padding: 0.7rem 2rem; position: sticky; top: 0; z-index: 1000; width: 100%; }
    .navbar-brand { color: #fff; font-weight: 700; font-size: 1.5rem; }
    .navbar-brand span { color: #ffe500; }
    .nav-link { color: #fff !important; font-weight: 500; }
    .nav-link:hover { text-decoration: underline; }
    .search-bar { width: 50%; }
    .search-input { border-radius: 0; border: none; height: 40px; font-size: 15px; }
    .search-btn { border-radius: 0; background: #ffe500; border: none; color: #000; font-weight: 600; }
    .banner { border-radius: 12px; overflow: hidden; margin-top: 1rem; }

    /* --- Category Bar --- */
    #categoryContainer {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 15px;
      margin-bottom: 30px;
    }

    .category-card {
      background: #fff;
      border-radius: 50px;
      text-align: center;
      padding: 0.7rem 1.5rem;
      font-weight: 600;
      color: #333;
      transition: all 0.2s ease;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      white-space: nowrap;
      border: 2px solid transparent;
      min-width: 110px;
    }

    .category-card:hover {
      background-color: #e8f0fe;
      transform: translateY(-2px);
    }

    .category-card.active {
      background-color: #2874f0;
      color: #fff;
      border-color: #2874f0;
      box-shadow: 0 3px 10px rgba(40, 116, 240, 0.4);
      transform: scale(1.05);
    }
    /* Professional Flipkart-style banner */
    .banner {
      border-radius: 12px;
      overflow: hidden;
      margin-top: 1rem;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    .banner-img {
      width: 100%;
      height: 300px;          /* desktop height */
      object-fit: cover;       /* crop nicely */
      transition: transform 0.3s ease;
    }

    /* Slight zoom on hover for a modern effect */
    .banner-img:hover {
      transform: scale(1.05);
    }

    /* Mobile responsiveness */
    @media (max-width: 768px) {
      .banner-img {
        height: 180px;        /* smaller on mobile */
      }
    }

    @media (max-width: 480px) {
      .banner-img {
        height: 140px;        /* very small phones */
      }
    }

    /* --- Books --- */
    .book-card { border: none; border-radius: 8px; transition: transform 0.2s; background: #fff; }
    .book-card:hover { transform: scale(1.04); box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); }
    .book-img { height: 200px; object-fit: contain; background: #f9f9f9; cursor:pointer; }
    .btn-buy { background-color: #ff6f00; border: none; color: white; font-weight: 600; width: 100%; height: 42px; }
    .btn-buy:hover { background-color: #e65100; }
    .btn-cart { background-color: #2874f0; border: none; color: white; font-weight: 600; width: 100%; height: 42px; }
    .btn-cart:hover { background-color: #0b60d1; }
    .btn-group-custom { display: flex; gap: 10px; margin-top: 8px; }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php">Book<span>Store</span></a>
    <form class="d-flex search-bar mx-auto" role="search" onsubmit="return filterBooks(event)">
      <input id="searchInput" class="form-control search-input" type="search" placeholder="Search for books, authors and more...">
      <button class="btn search-btn" type="submit">Search</button>
    </form>
    <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
      <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
      <li class="nav-item"><a class="nav-link" href="browse_books.php">Browse</a></li>
      <li class="nav-item"><a class="nav-link" href="my_orders.php">My Orders</a></li>
      <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
    </ul>
  </div>
</nav>

<!-- Banner -->
<div class="container mt-3">
  <div class="banner">
    <img src="uploads/Bookstore Banner.jpg" 
         class="img-fluid w-100 banner-img" 
         alt="Bookstore Banner">
  </div>
  <!-- Categories -->
  <h4 class="mt-4 mb-3">Shop by Category</h4>
  <div id="categoryContainer">
    <div class="category-card active" data-category="All">📚 All</div>
    <?php
    $categories = [
      'Fiction' => '📖',
      'Non-Fiction' => '🧠',
      'Science' => '🔬',
      'Romance' => '❤️',
      'Children' => '🐣',
      'Comics' => '🎨'
    ];
    foreach ($categories as $cat => $emoji):
    ?>
      <div class="category-card" data-category="<?= htmlspecialchars($cat) ?>">
        <?= $emoji ?> <?= htmlspecialchars($cat) ?>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Top Picks -->
  <h4 class="mb-3 mt-4">Top Picks For You</h4>

  <?php
  $books = $mysqli->query("SELECT id, title, author, price, cover, category FROM books ORDER BY id DESC LIMIT 20");
  $bookArray = [];
  while ($b = $books->fetch_assoc()) {
      $bookArray[] = $b;
  }
  ?>

  <div class="row g-4" id="bookContainer">
    <?php foreach($bookArray as $b): ?>
      <div class="col-6 col-md-3 book-card-container" data-category="<?= htmlspecialchars($b['category']) ?>">
        <div class="card book-card h-100 shadow-sm">
          <img src="uploads/<?= htmlspecialchars($b['cover']) ?>" class="card-img-top book-img book-img-clickable">

          <div class="card-body">
            <h6 class="card-title text-truncate"><?= htmlspecialchars($b['title']) ?></h6>
            <p class="text-muted small mb-1"><?= htmlspecialchars($b['author']) ?></p>

            <div class="btn-group-custom">
              <form method="post" action="place_orders.php" class="flex-fill">
                <input type="hidden" name="book_id" value="<?= $b['id'] ?>">
                <input type="hidden" name="action" value="buy">
                <button type="submit" class="btn btn-buy">
                  Buy at ₹<?= number_format($b['price'], 2) ?>
                </button>
              </form>

              <form method="post" action="place_orders.php" class="flex-fill">
                <input type="hidden" name="book_id" value="<?= $b['id'] ?>">
                <input type="hidden" name="action" value="cart">
                <button type="submit" class="btn btn-cart">
                  Add to Cart
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- Book Image Modal -->
<div class="modal fade" id="bookImageModal" tabindex="-1" aria-hidden="true">
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

<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('.category-card').forEach(card => {
  card.addEventListener('click', () => {
    const selected = card.dataset.category;
    
    document.querySelectorAll('.category-card').forEach(c => c.classList.remove('active'));
    card.classList.add('active');

    const books = document.querySelectorAll('.book-card-container');

    books.forEach(book => {
      book.style.transition = 'opacity 0.3s';
      book.style.opacity = '0';
    });

    setTimeout(() => {
      books.forEach(book => {
        if (selected === 'All' || book.dataset.category === selected) {
          book.style.display = 'block';
        } else {
          book.style.display = 'none';
        }
      });

      books.forEach(book => {
        if (book.style.display !== 'none') {
          book.style.opacity = '0';
          setTimeout(() => { book.style.opacity = '1'; }, 10);
        }
      });
    }, 300);
  });
});

// Book image click
document.querySelectorAll('.book-img-clickable').forEach(img => {
  img.addEventListener('click', () => {
    const src = img.getAttribute('src');
    document.getElementById('modalBookImg').setAttribute('src', src);
    const modal = new bootstrap.Modal(document.getElementById('bookImageModal'));
    modal.show();
  });
});

// Search function
function filterBooks(event) {
  event.preventDefault();
  const query = document.getElementById('searchInput').value.toLowerCase();
  const books = document.querySelectorAll('.book-card-container');

  books.forEach(book => {
    const title = book.querySelector('.card-title').textContent.toLowerCase();
    const author = book.querySelector('p.text-muted').textContent.toLowerCase();

    if (title.includes(query) || author.includes(query)) {
      book.style.display = 'block';
      book.style.opacity = '1';
    } else {
      book.style.display = 'none';
    }
  });

  document.querySelectorAll('.category-card').forEach(c => c.classList.remove('active'));
  document.querySelector('.category-card[data-category="All"]').classList.add('active');

  // Scroll to first visible book
  const firstVisible = Array.from(books).find(book => book.style.display !== 'none');
  if (firstVisible) {
    firstVisible.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  return false;
}

</script>
</body>
</html>
