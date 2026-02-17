//Menu active
const activePage = window.location.pathname;
const navLinks = document.querySelectorAll('nav a').forEach(link => {
  if(link.href.includes(`${activePage}`)){
    link.classList.add('active');
  }
});


//Menú de navegacion
const navToggle = document.querySelector(".nav-toggle")
const navMenu = document.querySelector(".nav-menu")

navToggle.addEventListener("click", () =>{
    navMenu.classList.toggle("nav-menu_visible");

    if (navMenu.classList.contains("nav-menu-visible")){
        navToggle.setAttribute("aria-label", "Cerrar menú");
    } else{
    navToggle.setAttribute("aria-label", "Abrir menú");
    }
});

//Productos Detalles
let bigImg = document.querySelector('.big-img');
let smallImg = document.querySelectorAll('.small-img');

smallImg.forEach((img)=>{
    img.addEventListener('click', function (ev) {
        let imgClicked = ev.target;
        bigImg.src = imgClicked.src;
    })
});

//Carrusel 
const productContainers = [...document.querySelectorAll('.product-container')];
const nxtBtn = [...document.querySelectorAll('.nxt-btn')];
const preBtn = [...document.querySelectorAll('.pre-btn')];

productContainers.forEach((item, i) => {
    let containerDimensions = item.getBoundingClientRect();
    let containerWidth = containerDimensions.width;

    nxtBtn[i].addEventListener('click', () => {
        item.scrollLeft += containerWidth;
    })

    preBtn[i].addEventListener('click', () => {
        item.scrollLeft -= containerWidth;
    })
});


//Carrito de compras
//abrir y cerrar carrito
let cartIcon = document.querySelector('#carrito-icon');
let cart = document.querySelector('.cart');
let closeCart = document.querySelector('#close__cart');

cartIcon.onclick = () =>{
    cart.classList.add("activ");
};

closeCart.onclick = () =>{
    cart.classList.remove("activ");
};


//añadir al carrito (AJAX con PHP)

const CartContent = document.querySelector('.cart__content');
const carritoItemsCount = document.querySelector('.contador__items');
const carritoTotalPrice = document.querySelector('.total__price');
const btnComprar = document.querySelector('.btn__comprar');

// Función para realizar solicitudes AJAX
async function sendCartAction(action, data = {}) {
    const formData = new FormData();
    formData.append('action', action);
    for (const key in data) {
        formData.append(key, data[key]);
    }

    try {
        const response = await fetch(PHP_CART_PROCESS_URL, {
            method: 'POST',
            body: formData
        });
        return await response.json();
    } catch (error) {
        console.error('Error en la solicitud AJAX del carrito:', error);
        return { success: false, message: 'Error de conexión.' };
    }
}

// Función para actualizar la UI del carrito
function updateCartUI(cartData) {
    CartContent.innerHTML = ''; // Limpiar el contenido actual del carrito
    if (cartData.cart_items && cartData.cart_items.length > 0) {
        cartData.cart_items.forEach(item => {
            const detalles = document.createElement('div');
            detalles.classList.add('cart__box');
            const Content = `
                <img src="${item.image}" alt="${item.name}" class="cart__img">
                <div class="details__box">
                    <div class="cart__product__title">${item.name}</div>
                    <div class="cart__price">$${item.price}</div>
                    <input type="number" value="${item.quantity}" class="cart__cantidad" data-product-id="${item.id}">
                </div>
                <!--Remover del carrito-->
                <i class="fa-solid fa-trash cart__remove" data-product-id="${item.id}"></i>
            `;
            detalles.innerHTML = Content;
            CartContent.append(detalles);

            detalles.querySelector(".cart__remove").addEventListener('click', removeItemCarrito);
            detalles.querySelector(".cart__cantidad").addEventListener('change', updateItemQuantity);
        });
        btnComprar.style.display = 'block'; // Mostrar botón de comprar
    } else {
        CartContent.innerHTML = '<p class="text-center p-3">El carrito está vacío.</p>';
        btnComprar.style.display = 'none'; // Ocultar botón de comprar
    }
    carritoItemsCount.textContent = cartData.cart_count;
    carritoTotalPrice.textContent = `$${cartData.cart_total}`;

    // Mostrar mensaje de SweetAlert2 si lo hay
    if (cartData.message) {
        Swal.fire({
            position: 'bottom-end',
            icon: cartData.success ? 'success' : 'error',
            backdrop: false,
            title: cartData.message,
            showConfirmButton: false,
            timer: 1500,
            toast: true
        });
    }
}

// Event listener para añadir al carrito
document.addEventListener('click', async (e) => {
    if (e.target.classList.contains('btn-agregar')) {
        const button = e.target;
        const productId = button.dataset.productId;
        if (productId) {
            const result = await sendCartAction('add', { product_id: productId, quantity: 1 });
            if (result.success) {
                updateCartUI(result);
            } else {
                Swal.fire({
                    position: 'bottom-end',
                    icon: 'error',
                    backdrop: false,
                    title: result.message || 'Error al añadir el producto.',
                    showConfirmButton: false,
                    timer: 1500,
                    toast: true
                });
            }
        }
    }
});


// Event listener para eliminar del carrito
async function removeItemCarrito(e) {
    const productId = e.target.dataset.productId;
    if (productId) {
        const result = await sendCartAction('remove', { product_id: productId });
        if (result.success) {
            updateCartUI(result);
        } else {
            Swal.fire({
                position: 'bottom-end',
                icon: 'error',
                backdrop: false,
                title: result.message || 'Error al eliminar el producto.',
                showConfirmButton: false,
                timer: 1500,
                toast: true
            });
        }
    }
}

// Event listener para actualizar cantidad
async function updateItemQuantity(e) {
    const productId = e.target.dataset.productId;
    const newQuantity = parseInt(e.target.value);

    if (productId && !isNaN(newQuantity)) {
        const result = await sendCartAction('update_quantity', { product_id: productId, quantity: newQuantity });
        if (result.success) {
            updateCartUI(result);
        } else {
            Swal.fire({
                position: 'bottom-end',
                icon: 'error',
                backdrop: false,
                title: result.message || 'Error al actualizar la cantidad.',
                showConfirmButton: false,
                timer: 1500,
                toast: true
            });
        }
    }
}

// Cargar el estado inicial del carrito al cargar la página
document.addEventListener('DOMContentLoaded', async () => {
    const result = await sendCartAction('get_cart');
    if (result.success) {
        updateCartUI(result);
    } else {
        console.error('Error al cargar el carrito inicial:', result.message);
    }
});