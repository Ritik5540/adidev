$(document).ready(function () {
  let isLoading = false;
  const currency = $("meta[name='currency']").attr("content") || "INR";

  function pricing_format(amount, currency = 'INR') {

    amount = parseFloat(amount || 0);

    if (currency === 'USD') {
        // US format
        return '$' + amount.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    // Default INR
    return '₹' + amount.toLocaleString('en-IN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
  }

  // Function to get current filters
  function getFilters() {
    let colors = [];
    $(".color-filter:checked").each(function () {
      colors.push($(this).val());
    });

    let ratings = [];
    $(".rating-filter:checked").each(function () {
      ratings.push($(this).val());
    });

    let stock = "";
    if ($("#stock_in_stock").is(":checked")) {
      stock = "in_stock";
    } else if ($("#stock_out_of_stock").is(":checked")) {
      stock = "out_of_stock";
    }

    return {
      min_price: $("#min_price_input").val() || 0,
      max_price: $("#max_price_input").val() || 100000,
      colors: colors.join(","),
      ratings: ratings.join(","),
      stock: stock,
      sort: $("#sort_by").val(),
      per_page: $("#per_page").val(),
      page: 1,
    };
  }

  // Function to load products via AJAX
  function loadProducts() {
    if (isLoading) return;
    isLoading = true;

    $("#products-grid, #products-list").html(
      '<div class="col-12 text-center loading"><i class="fas fa-spinner fa-spin"></i> Loading products...</div>',
    );

    let filters = getFilters();
    let urlParams = new URLSearchParams(window.location.search);

    // Add category parameters
    if (urlParams.has("sub")) {
      filters.sub = urlParams.get("sub");
    }
    if (urlParams.has("category")) {
      filters.category = urlParams.get("category");
    }

    $.ajax({
      url: "ajax/get_products.php",
      type: "GET",
      data: filters,
      dataType: "json",
      success: function (response) {
        if (response.success) {
          // console.log(response.products);
          updateProductsGrid(response.products);
          updateProductsList(response.products);
          updatePagination(response.total_pages, response.current_page);
          updateProductCount(
            response.total,
            response.per_page,
            response.current_page,
          );
        }
        isLoading = false;
      },
      error: function () {
        isLoading = false;
        $("#products-grid, #products-list").html(
          '<div class="col-12 text-center"><p class="text-danger">Error loading products. Please try again.</p></div>',
        );
      },
    });
  }

  // Update grid view
  function updateProductsGrid(products) {
    let html = "";
    if (products.length === 0) {
      html =
        '<div class="col-12 text-center"><div class="empty-state"><i class="fas fa-box-open"></i><h3>No Products Found</h3><p>Try adjusting your filters or browse other categories.</p></div></div>';
    } else {
      // console.log(products);
      products.forEach((product) => {
        let discount = "";
        if (product.is_on_sale && product.mrp > product.retail_price) {
          let percent = Math.round(
            ((product.mrp - product.retail_price) / product.mrp) * 100,
          );
          discount = `<li class="discount"> -${percent}%</li>`;
        }

        let stockBadge = "";
        if (product.stock_quantity <= 0) {
          stockBadge = '<div class="out_of_stock"><p>out of stock</p></div>';
        }

        let ratingHtml = "";
        for (let i = 1; i <= 5; i++) {
          ratingHtml += `<i class="fas fa-star${i <= Math.round(product.avg_rating) ? "" : "-o"}"></i>`;
        }

        html += `
                <div class="col-xxl-3 col-6 col-md-4 col-lg-6 col-xl-4 wow fadeInUp">
                    <div class="product_item_2 product_item">
                        <div class="product_img">
                            <img src="${product.main_image || "assets/images/product_placeholder.jpg"}" alt="${product.name}" class="img-fluid w-100">
                            <ul class="discount_list">
                                ${product.is_new ? '<li class="new"> new</li>' : ""}
                                ${discount}
                            </ul>
                            <ul class="btn_list">
                                <li>
                                    <a href="#">
                                        <img src="assets/images/love_icon_white.svg" class="add-to-wishlist" alt="Love" data-id="${product.id}"
                                            class="img-fluid">
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <img src="assets/images/cart_icon_white.svg" class="add-to-cart" alt="Love" data-id="${product.id}"
                                            class="img-fluid">
                                    </a>
                                </li>
                            </ul>
                        </div>
<div class="product_text">
                            <a class="title" href="shop_details.php?id=${product.encrypted_id}">
                                ${product.name}
                            </a>

                            <p class="price">
                                ${pricing_format(product.price, currency)}
                                ${product.mrp > product.price ? `<del>${pricing_format(product.mrp, currency)}</del>` : ""}
                            </p>

                            <p class="rating">
                                ${ratingHtml}
                                <span>(${product.review_count} reviews)</span>
                            </p>
                        </div>

                        ${stockBadge}
                    </div>
                </div>
            `;
      });
    }
    $("#products-grid").html(html);
  }

  // Update list view
  function updateProductsList(products) {
    let html = "";
    if (products.length === 0) {
      html =
        '<div class="col-12 text-center"><div class="empty-state"><i class="fas fa-box-open"></i><h3>No Products Found</h3><p>Try adjusting your filters or browse other categories.</p></div></div>';
    } else {
      products.forEach((product) => {
        let discount = "";
        if (product.is_on_sale && product.mrp > product.retail_price) {
          let percent = Math.round(
            ((product.mrp - product.retail_price) / product.mrp) * 100,
          );
          discount = `<li class="discount"> -${percent}%</li>`;
        }

        let stockBadge = "";
        if (product.stock_quantity <= 0) {
          stockBadge = '<div class="out_of_stock"><p>out of stock</p></div>';
        }

        let ratingHtml = "";
        for (let i = 1; i <= 5; i++) {
          ratingHtml += `<i class="fas fa-star${i <= Math.round(product.avg_rating) ? "" : "-o"}"></i>`;
        }

        html += `
                <div class="col-12">
                    <div class="product_list_item product_item_2 product_item">
                        <div class="row align-items-center">
                            <div class="col-md-5 col-sm-6 col-xxl-4">
                                <div class="product_img">
                                    <img src="${product.image_url || "assets/images/product_placeholder.jpg"}" alt="${product.name}" class="img-fluid w-100">
                                    <ul class="discount_list">
                                        ${product.is_new ? '<li class="new"> new</li>' : ""}
                                        ${discount}
                                    </ul>
                            <ul class="btn_list">
                                <li>
                                    <a href="#">
                                        <img src="assets/images/love_icon_white.svg" class="add-to-wishlist" alt="Love" data-id="${product.id}"
                                            class="img-fluid">
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <img src="assets/images/cart_icon_white.svg" class="add-to-cart" alt="Love" data-id="${product.id}"
                                            class="img-fluid">
                                    </a>
                                </li>
                            </ul>
                                </div>
                            </div>
                            <div class="col-md-7 col-sm-6 col-xxl-8">
                                <div class="product_text">
                            <a class="title" href="shop_details.php?id=${product.encrypted_id}">
                                ${product.name}
                            </a>

                            <p class="price">
                                ${pricing_format(product.price, currency)}
                                ${product.mrp > product.price ? `<del>${pricing_format(product.mrp, currency)}</del>` : ""}
                            </p>

                            <p class="rating">
                                ${ratingHtml}
                                <span>(${product.review_count} reviews)</span>
                            </p>
                        </div>

                        ${stockBadge}
                            </div>
                        </div>
                    </div>
                </div>
            `;
      });
    }
    $("#products-list").html(html);
  }

  // Update pagination
  function updatePagination(totalPages, currentPage) {
    let paginationHtml = "";

    paginationHtml += `<li class="page-item ${currentPage <= 1 ? "disabled" : ""}">
        <a class="page-link" href="#" data-page="${currentPage - 1}"><i class="far fa-arrow-left"></i></a>
    </li>`;

    let startPage = Math.max(1, currentPage - 2);
    let endPage = Math.min(totalPages, currentPage + 2);

    if (startPage > 1) {
      paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="1">01</a></li>`;
      if (startPage > 2) {
        paginationHtml += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
      }
    }

    for (let i = startPage; i <= endPage; i++) {
      paginationHtml += `<li class="page-item ${i == currentPage ? "active" : ""}">
            <a class="page-link" href="#" data-page="${i}">${String(i).padStart(2, "0")}</a>
        </li>`;
    }

    if (endPage < totalPages) {
      if (endPage < totalPages - 1) {
        paginationHtml += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
      }
      paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a></li>`;
    }

    paginationHtml += `<li class="page-item ${currentPage >= totalPages ? "disabled" : ""}">
        <a class="page-link" href="#" data-page="${currentPage + 1}"><i class="far fa-arrow-right"></i></a>
    </li>`;

    $("#pagination, #pagination-list").html(paginationHtml);

    // Bind pagination click events
    $(".page-link").on("click", function (e) {
      e.preventDefault();
      let page = $(this).data("page");
      if (page && page > 0) {
        let filters = getFilters();
        filters.page = page;
        // Reload with new page
        loadProducts();
      }
    });
  }

  // Update product count
  function updateProductCount(total, perPage, currentPage) {
    let start = (currentPage - 1) * perPage + 1;
    let end = Math.min(currentPage * perPage, total);
    $(".product_page_top_button p").text(
      `Showing ${start}–${end} of ${total} results`,
    );
  }

  // Helper function to get color code
  function getColorCode(color) {
    const colorMap = {
      red: "#DB4437",
      green: "#41CF0F",
      gray: "#8e8e8e",
      orange: "#ffa500",
      purple: "#B615FD",
      yellow: "#FFD747",
      olive: "#AB9774",
      "dark blue": "#1C58F2",
      blue: "#1C58F2",
      black: "#000000",
      white: "#FFFFFF",
      brown: "#8B4513",
      pink: "#FF69B4",
    };
    return colorMap[color.toLowerCase()] || "#6c757d";
  }

  // Apply price filter
  $("#apply_price_filter").on("click", function () {
    loadProducts();
  });

  // Filter change events
  $(".color-filter, .rating-filter, .stock-filter, #sort_by, #per_page").on(
    "change",
    function () {
      loadProducts();
    },
  );

  // Price input enter key
  $("#min_price_input, #max_price_input").on("keypress", function (e) {
    if (e.which === 13) {
      loadProducts();
    }
  });

  // Quick view functionality
  $(document).on("click", ".quick-view", function (e) {
    e.preventDefault();
    let productId = $(this).data("id");
    // Implement quick view modal here
    alert("Quick view for product ID: " + productId);
  });

  // Add to wishlist
  $(document).on("click", ".add-to-wishlist", function (e) {
    e.preventDefault();
    let productId = $(this).data("id");
    $.ajax({
      url: "ajax/add_to_wishlist.php",
      type: "POST",
      data: {
        product_id: productId,
      },
      success: function (response) {
        if (response.redirect) {
          window.location.href = response.redirect_url;
          return;
        }
        if (response.success) {
          showToast("Product added to wishlist!", "success");
          updateCartCount();
        } else {
          alert("Please login to add to wishlist");
        }
      },
    });
  });

  // Add to cart
  $(document).on("click", ".add-to-cart", function (e) {
    e.preventDefault();
    let productId = $(this).data("id");
    let quantity = $(this).data("quantity") || 1;
    $.ajax({
      url: "ajax/add_to_cart.php",
      type: "POST",
      data: {
        product_id: productId,
        quantity: quantity,
      },
      success: function (response) {
        // 🔐 Handle login redirect
        if (response.redirect) {
          window.location.href = response.redirect_url;
          return;
        }
        if (response.success) {
          showToast("Product added to cart!", "success");
          updateCartCount();
        }
      },
    });
  });

  // Add to cart
  $(document).on("click", ".add-to-cart-wislisht", function (e) {
    e.preventDefault();
    const btn = e.target.closest(".add-to-cart-wislisht");
    let productId = $(this).data("id");
    const row = btn.closest("tr");
    const input = row.querySelector(".qty-input");
    const quantity = parseInt(input.value) || 1;
    $.ajax({
      url: "ajax/add_to_cart.php",
      type: "POST",
      data: {
        product_id: productId,
        quantity: quantity,
      },
      success: function (response) {
        // 🔐 Handle login redirect
        if (response.redirect) {
          window.location.href = response.redirect_url;
          return;
        }
        if (response.success) {
          showToast("Product added to cart!", "success");
          updateCartCount();
        }
      },
    });
  });

  function showToast(message, type = "success") {
    let toast = $(`
            <div class="custom-toast ${type}">
                <span class="toast-message">${message}</span>
                <span class="toast-close">&times;</span>
            </div>
        `);

    $("#toast-container").append(toast);

    setTimeout(() => toast.addClass("show"), 100);

    setTimeout(() => {
      toast.removeClass("show");
      setTimeout(() => toast.remove(), 300);
    }, 3000);

    toast.find(".toast-close").click(() => toast.remove());
  }

  // Update cart count
  function updateCartCount() {
    $.ajax({
      url: "ajax/get_cart_count.php",
      type: "GET",
      dataType: "json",
      cache: false,
      success: function (res) {
        if (res.success) {
          $(".cart-count").text(res.cart_count || 0);
          $(".wishlist-count").text(res.wishlist_count || 0);
        }
      },
      error: function () {
        console.warn("Count fetch failed");
      },
    });
  }
});
