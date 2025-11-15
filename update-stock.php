<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Каталог товаров - Бронирование</title>
    <style>
        /* Все стили остаются такими же как в предыдущем коде */
        :root {
            --white: #ffffff;
            --green: #4CAF50;
            --kraft: #f5e8d0;
            --dark-green: #2E7D32;
            --light-green: #E8F5E9;
            --warning: #ff9800;
            --error: #f44336;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }
        
        body {
            background-color: var(--white);
            color: #333;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        /* ... все остальные стили без изменений ... */
        
    </style>
</head>
<body>
    <!-- Шапка с корзиной -->
    <header>
        <div class="header-content">
            <div class="logo">ЭкоМаркет</div>
            <div class="cart-icon" id="cart-icon">
                🛒
                <span class="cart-count" id="cart-count">0</span>
            </div>
        </div>
    </header>

    <!-- Баннер -->
    <div class="banner">
        <img src="images/banner.jpg" alt="ЭкоМаркет - Натуральные товары" class="banner-image">
        <div class="banner-overlay">
            <h1>ЭкоМаркет</h1>
            <p>Натуральные товары для здорового образа жизни</p>
        </div>
    </div>

    <!-- Основной контент -->
    <main>
        <div class="container">
            <div class="products-grid" id="products-container">
                <!-- Карточки товаров будут добавлены с помощью JavaScript -->
            </div>
        </div>
    </main>

    <!-- Модальное окно корзины -->
    <div class="cart-modal" id="cart-modal">
        <div class="cart-content">
            <div class="cart-header">
                <h2 class="cart-title">Корзина</h2>
                <button class="close-cart" id="close-cart">×</button>
            </div>
            <div class="cart-items" id="cart-items">
                <!-- Товары в корзине будут добавлены с помощью JavaScript -->
            </div>
            <div class="cart-total" id="cart-total">
                Общая сумма: 0 руб.
            </div>
            <button class="reserve-all-btn" id="reserve-all-btn">Забронировать все</button>
            <div class="stock-warning" id="stock-warning"></div>
        </div>
    </div>

    <!-- Футер -->
    <footer>
        <div class="container">
            <p class="footer-text">Создано Dyndovsk Studio</p>
        </div>
    </footer>

    <script>
        // Данные товаров
        let products = [];
        let cart = [];

        // Загрузка данных о товарах с сервера
        async function loadProducts() {
            try {
                const response = await fetch('update-stock.php');
                if (!response.ok) {
                    throw new Error('Ошибка загрузки данных');
                }
                const data = await response.json();
                products = data.products;
                initProducts();
            } catch (error) {
                console.error('Ошибка загрузки данных:', error);
                // Запасной вариант - используем локальные данные
                products = getDefaultProducts();
                initProducts();
            }
        }

        // Функция для получения данных по умолчанию
        function getDefaultProducts() {
            return [
                {
                    id: 1,
                    title: "Набор эко-посуды",
                    price: 1500,
                    stock: 10,
                    reserved: 0,
                    images: [
                        "https://via.placeholder.com/300x200/4CAF50/FFFFFF?text=Эко-посуда+1",
                        "https://via.placeholder.com/300x200/2E7D32/FFFFFF?text=Эко-посуда+2",
                        "https://via.placeholder.com/300x200/81C784/FFFFFF?text=Эко-посуда+3"
                    ]
                },
                {
                    id: 2,
                    title: "Бамбуковая зубная щетка",
                    price: 250,
                    stock: 25,
                    reserved: 0,
                    images: [
                        "https://via.placeholder.com/300x200/4CAF50/FFFFFF?text=Зубная+щетка+1",
                        "https://via.placeholder.com/300x200/2E7D32/FFFFFF?text=Зубная+щетка+2"
                    ]
                },
                {
                    id: 3,
                    title: "Многоразовые сумки",
                    price: 450,
                    stock: 8,
                    reserved: 0,
                    images: [
                        "https://via.placeholder.com/300x200/4CAF50/FFFFFF?text=Сумки+1",
                        "https://via.placeholder.com/300x200/2E7D32/FFFFFF?text=Сумки+2",
                        "https://via.placeholder.com/300x200/81C784/FFFFFF?text=Сумки+3",
                        "https://via.placeholder.com/300x200/388E3C/FFFFFF?text=Сумки+4"
                    ]
                },
                {
                    id: 4,
                    title: "Эко-косметика",
                    price: 1200,
                    stock: 8,
                    reserved: 0,
                    images: [
                        "https://via.placeholder.com/300x200/4CAF50/FFFFFF?text=Косметика+1",
                        "https://via.placeholder.com/300x200/2E7D32/FFFFFF?text=Косметика+2"
                    ]
                }
            ];
        }

        // Функция для сохранения бронирований на сервере
        async function saveReservations(reservations) {
            try {
                const response = await fetch('update-stock.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'reserve',
                        reservations: reservations
                    })
                });

                if (!response.ok) {
                    throw new Error('Ошибка сохранения бронирования');
                }

                const result = await response.json();
                return result.success;
            } catch (error) {
                console.error('Ошибка сохранения бронирования:', error);
                return false;
            }
        }

        // Функция для получения доступного количества товара
        function getAvailableQuantity(productId) {
            const product = products.find(p => p.id === productId);
            if (!product) return 0;
            
            // Учитываем уже забронированные товары
            const reservedInCart = cart.find(item => item.id === productId)?.quantity || 0;
            return product.stock - product.reserved - reservedInCart;
        }

        // Функция для создания карточки товара
        function createProductCard(product) {
            const available = getAvailableQuantity(product.id);
            
            const card = document.createElement('div');
            card.className = 'product-card';
            card.innerHTML = `
                <div class="product-gallery">
                    <div class="gallery-images">
                        ${product.images.map(img => `<img src="${img}" alt="${product.title}" class="gallery-image" onerror="this.src='https://via.placeholder.com/300x200/4CAF50/FFFFFF?text=Изображение+не+загружено'">`).join('')}
                    </div>
                    <div class="gallery-nav">
                        <button class="gallery-btn prev-btn">‹</button>
                        <button class="gallery-btn next-btn">›</button>
                    </div>
                    <div class="gallery-dots">
                        ${product.images.map((_, index) => `<span class="dot ${index === 0 ? 'active' : ''}" data-index="${index}"></span>`).join('')}
                    </div>
                </div>
                <div class="product-info">
                    <h3 class="product-title">${product.title}</h3>
                    <div class="product-price">${product.price} руб.</div>
                    <div class="product-stock">Всего на складе: ${product.stock} шт.</div>
                    ${product.reserved > 0 ? `<div class="product-reserved">Уже забронировано: ${product.reserved} шт.</div>` : ''}
                    <div class="product-available">Доступно для брони: ${available} шт.</div>
                    <div class="add-to-cart-form">
                        <div class="quantity-selector">
                            <button class="quantity-btn minus-btn" ${available <= 0 ? 'disabled' : ''}>-</button>
                            <input type="number" class="quantity-input" value="1" min="1" max="${available}" ${available <= 0 ? 'disabled' : ''}>
                            <button class="quantity-btn plus-btn" ${available <= 0 ? 'disabled' : ''}>+</button>
                        </div>
                        <button class="add-to-cart-btn" data-product-id="${product.id}" ${available <= 0 ? 'disabled' : ''}>
                            ${available <= 0 ? 'Нет в наличии' : 'Добавить в корзину'}
                        </button>
                        <div class="error-message">Нельзя добавить больше, чем есть в наличии</div>
                    </div>
                </div>
            `;
            
            return card;
        }

        // Функция для инициализации галереи
        function initGallery(galleryElement) {
            const imagesContainer = galleryElement.querySelector('.gallery-images');
            const prevBtn = galleryElement.querySelector('.prev-btn');
            const nextBtn = galleryElement.querySelector('.next-btn');
            const dots = galleryElement.querySelectorAll('.dot');
            const images = galleryElement.querySelectorAll('.gallery-image');
            
            let currentIndex = 0;
            const totalImages = images.length;
            
            function updateGallery() {
                imagesContainer.style.transform = `translateX(-${currentIndex * 100}%)`;
                
                dots.forEach((dot, index) => {
                    dot.classList.toggle('active', index === currentIndex);
                });
            }
            
            prevBtn.addEventListener('click', () => {
                currentIndex = (currentIndex - 1 + totalImages) % totalImages;
                updateGallery();
            });
            
            nextBtn.addEventListener('click', () => {
                currentIndex = (currentIndex + 1) % totalImages;
                updateGallery();
            });
            
            dots.forEach((dot, index) => {
                dot.addEventListener('click', () => {
                    currentIndex = index;
                    updateGallery();
                });
            });
        }

        // Функция для инициализации формы добавления в корзину
        function initAddToCartForm(formElement, product) {
            const minusBtn = formElement.querySelector('.minus-btn');
            const plusBtn = formElement.querySelector('.plus-btn');
            const quantityInput = formElement.querySelector('.quantity-input');
            const addToCartBtn = formElement.querySelector('.add-to-cart-btn');
            const errorMessage = formElement.querySelector('.error-message');
            
            function updateFormState() {
                const available = getAvailableQuantity(product.id);
                const currentValue = parseInt(quantityInput.value);
                
                minusBtn.disabled = available <= 0 || currentValue <= 1;
                plusBtn.disabled = available <= 0 || currentValue >= available;
                quantityInput.disabled = available <= 0;
                quantityInput.max = available;
                
                if (currentValue > available) {
                    quantityInput.value = available;
                }
                
                addToCartBtn.disabled = available <= 0;
                addToCartBtn.textContent = available <= 0 ? 'Нет в наличии' : 'Добавить в корзину';
            }
            
            minusBtn.addEventListener('click', () => {
                let value = parseInt(quantityInput.value);
                if (value > 1) {
                    quantityInput.value = value - 1;
                    updateFormState();
                }
            });
            
            plusBtn.addEventListener('click', () => {
                let value = parseInt(quantityInput.value);
                const available = getAvailableQuantity(product.id);
                if (value < available) {
                    quantityInput.value = value + 1;
                    updateFormState();
                }
            });
            
            quantityInput.addEventListener('input', () => {
                validateQuantity();
                updateFormState();
            });
            
            function validateQuantity() {
                const value = parseInt(quantityInput.value);
                const available = getAvailableQuantity(product.id);
                
                if (isNaN(value) || value < 1) {
                    quantityInput.value = 1;
                } else if (value > available) {
                    quantityInput.value = available;
                    showError();
                } else {
                    hideError();
                }
            }
            
            function showError() {
                errorMessage.style.display = 'block';
            }
            
            function hideError() {
                errorMessage.style.display = 'none';
            }
            
            addToCartBtn.addEventListener('click', () => {
                const quantity = parseInt(quantityInput.value);
                const available = getAvailableQuantity(product.id);
                
                if (quantity > available) {
                    showError();
                    return;
                }
                
                addToCart(product.id, product.title, product.price, quantity);
                quantityInput.value = 1;
                hideError();
                updateFormState();
                
                // Анимация добавления
                addToCartBtn.textContent = 'Добавлено!';
                setTimeout(() => {
                    addToCartBtn.textContent = 'Добавить в корзину';
                }, 1000);
            });
            
            // Инициализируем состояние формы
            updateFormState();
        }

        // Функция добавления товара в корзину
        function addToCart(productId, productTitle, productPrice, quantity) {
            const existingItem = cart.find(item => item.id === productId);
            
            if (existingItem) {
                existingItem.quantity += quantity;
            } else {
                cart.push({
                    id: productId,
                    title: productTitle,
                    price: productPrice,
                    quantity: quantity
                });
            }
            
            updateCartUI();
            updateAllProductCards();
        }

        // Функция обновления количества товара в корзине
        function updateCartItemQuantity(productId, newQuantity) {
            if (newQuantity <= 0) {
                removeFromCart(productId);
                return;
            }
            
            const available = getAvailableQuantity(productId);
            if (newQuantity > available) {
                newQuantity = available;
            }
            
            const item = cart.find(item => item.id === productId);
            if (item) {
                item.quantity = newQuantity;
            }
            
            updateCartUI();
            updateAllProductCards();
        }

        // Функция удаления товара из корзины
        function removeFromCart(productId) {
            cart = cart.filter(item => item.id !== productId);
            updateCartUI();
            updateAllProductCards();
        }

        // Функция обновления всех карточек товаров
        function updateAllProductCards() {
            const productsContainer = document.getElementById('products-container');
            productsContainer.innerHTML = '';
            
            products.forEach(product => {
                const card = createProductCard(product);
                productsContainer.appendChild(card);
                
                const gallery = card.querySelector('.product-gallery');
                initGallery(gallery);
                
                const form = card.querySelector('.add-to-cart-form');
                initAddToCartForm(form, product);
            });
        }

        // Функция обновления интерфейса корзины
        function updateCartUI() {
            const cartCount = document.getElementById('cart-count');
            const cartItems = document.getElementById('cart-items');
            const cartTotal = document.getElementById('cart-total');
            const reserveAllBtn = document.getElementById('reserve-all-btn');
            const stockWarning = document.getElementById('stock-warning');
            
            const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
            cartCount.textContent = totalItems;
            
            if (cart.length === 0) {
                cartItems.innerHTML = '<div class="empty-cart">Корзина пуста</div>';
                reserveAllBtn.disabled = true;
                stockWarning.textContent = '';
            } else {
                cartItems.innerHTML = cart.map(item => {
                    const available = getAvailableQuantity(item.id);
                    const product = products.find(p => p.id === item.id);
                    const maxAvailable = product ? product.stock - product.reserved : 0;
                    
                    return `
                        <div class="cart-item">
                            <div class="cart-item-info">
                                <div class="cart-item-name">${item.title}</div>
                                <div class="cart-item-stock">
                                    Доступно: ${available} из ${maxAvailable} шт.
                                </div>
                                <div class="cart-item-price">${item.price} руб. × ${item.quantity} шт.</div>
                            </div>
                            <div class="cart-item-quantity">
                                <button class="cart-quantity-btn minus-cart-btn" data-product-id="${item.id}" ${item.quantity <= 1 ? 'disabled' : ''}>-</button>
                                <input type="number" class="cart-quantity-input" value="${item.quantity}" min="1" max="${available}" data-product-id="${item.id}">
                                <button class="cart-quantity-btn plus-cart-btn" data-product-id="${item.id}" ${item.quantity >= available ? 'disabled' : ''}>+</button>
                                <button class="remove-item" data-product-id="${item.id}">🗑️</button>
                            </div>
                        </div>
                    `;
                }).join('');
                
                reserveAllBtn.disabled = false;
                
                // Проверяем наличие предупреждений о количестве
                const warnings = [];
                cart.forEach(item => {
                    const available = getAvailableQuantity(item.id);
                    if (item.quantity > available) {
                        warnings.push(`В корзине ${item.quantity} "${item.title}", но доступно только ${available}`);
                    }
                });
                
                if (warnings.length > 0) {
                    stockWarning.innerHTML = warnings.join('<br>');
                    reserveAllBtn.disabled = true;
                } else {
                    stockWarning.textContent = '';
                }
                
                // Обработчики для элементов корзины
                document.querySelectorAll('.minus-cart-btn').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        const productId = parseInt(e.target.dataset.productId);
                        const item = cart.find(item => item.id === productId);
                        if (item) {
                            updateCartItemQuantity(productId, item.quantity - 1);
                        }
                    });
                });
                
                document.querySelectorAll('.plus-cart-btn').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        const productId = parseInt(e.target.dataset.productId);
                        const item = cart.find(item => item.id === productId);
                        if (item) {
                            updateCartItemQuantity(productId, item.quantity + 1);
                        }
                    });
                });
                
                document.querySelectorAll('.cart-quantity-input').forEach(input => {
                    input.addEventListener('change', (e) => {
                        const productId = parseInt(e.target.dataset.productId);
                        const newQuantity = parseInt(e.target.value);
                        if (!isNaN(newQuantity) && newQuantity > 0) {
                            updateCartItemQuantity(productId, newQuantity);
                        }
                    });
                });
                
                document.querySelectorAll('.remove-item').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        const productId = parseInt(e.target.dataset.productId);
                        removeFromCart(productId);
                    });
                });
            }
            
            const totalAmount = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            cartTotal.textContent = `Общая сумма: ${totalAmount} руб.`;
        }

        // Функция бронирования всех товаров
        async function reserveAllItems() {
            if (cart.length === 0) return;
            
            // Проверяем, что все товары доступны в нужном количестве
            for (const item of cart) {
                const available = getAvailableQuantity(item.id);
                if (item.quantity > available) {
                    alert(`Недостаточно товара "${item.title}". Доступно: ${available} шт.`);
                    return;
                }
            }
            
            // Формируем сообщение для Telegram
            let message = "Здравствуйте! Хочу забронировать следующие товары:\n\n";
            
            cart.forEach(item => {
                message += `• ${item.title} - ${item.quantity} шт. × ${item.price} руб. = ${item.price * item.quantity} руб.\n`;
            });
            
            const totalAmount = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            message += `\nИтого: ${totalAmount} руб.`;
            
            // Сохраняем бронирования на сервере
            const reservations = cart.map(item => ({
                id: item.id,
                quantity: item.quantity
            }));
            
            const saveSuccess = await saveReservations(reservations);
            
            if (saveSuccess) {
                // Обновляем локальные данные после успешного сохранения
                await loadProducts();
                
                // Кодируем сообщение для URL
                const encodedMessage = encodeURIComponent(message);
                const telegramUrl = `https://t.me/your_username?text=${encodedMessage}`;
                
                // Открываем Telegram
                window.open(telegramUrl, '_blank');
                
                // Очищаем корзину после бронирования
                cart = [];
                updateCartUI();
                updateAllProductCards();
                closeCartModal();
                
                // Показываем подтверждение
                alert('Ваш заказ отправлен в Telegram! Товары забронированы.');
            } else {
                alert('Ошибка сохранения бронирования. Пожалуйста, попробуйте еще раз.');
            }
        }

        // Функции для работы с модальным окном корзины
        function openCartModal() {
            document.getElementById('cart-modal').style.display = 'flex';
        }

        function closeCartModal() {
            document.getElementById('cart-modal').style.display = 'none';
        }

        // Инициализация товаров
        function initProducts() {
            const productsContainer = document.getElementById('products-container');
            
            products.forEach(product => {
                const card = createProductCard(product);
                productsContainer.appendChild(card);
                
                const gallery = card.querySelector('.product-gallery');
                initGallery(gallery);
                
                const form = card.querySelector('.add-to-cart-form');
                initAddToCartForm(form, product);
            });
        }

        // Инициализация страницы
        document.addEventListener('DOMContentLoaded', () => {
            const cartIcon = document.getElementById('cart-icon');
            const closeCartBtn = document.getElementById('close-cart');
            const reserveAllBtn = document.getElementById('reserve-all-btn');
            
            // Загружаем товары
            loadProducts();
            
            // Обработчики для корзины
            cartIcon.addEventListener('click', openCartModal);
            closeCartBtn.addEventListener('click', closeCartModal);
            reserveAllBtn.addEventListener('click', reserveAllItems);
            
            document.getElementById('cart-modal').addEventListener('click', (e) => {
                if (e.target === document.getElementById('cart-modal')) {
                    closeCartModal();
                }
            });
            
            // Инициализируем UI корзины
            updateCartUI();
        });
    </script>
</body>
</html>
