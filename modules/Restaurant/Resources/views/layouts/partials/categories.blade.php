
@php
    use Illuminate\Support\Str;
    $path = explode('/', request()->path());
    $path[1] = (array_key_exists(1, $path)> 0)?$path[1]:'';
    $path[0] = ($path[0] === '')?'menu':$path[0];
@endphp
<div class="container">
    <div class="row">
        <nav class="main-nav flex-grow-1">
                    <ul class="all-category my-0 pb-4">
                      <li class="title-category">Nuestras Especialidades</li>
                      <li>
                          <a href="{{ route('tenant.restaurant.menu') }}" class="{{ $path[1] == '' ? 'bg-success text-light' : '' }}">Ver todos</a>
                      </li>
                    </ul>
                    <div class="container">
                    <ul id="scrollContainer" class="menu restaurante sf-arrows sf-js-enabled" style="touch-action: pan-y;">
                        @foreach ($categories as $category)
                            <li class="menu-item"> 
                                <a href="{{ route('tenant.restaurant.menu', ['name' => Str::slug($category->name, '-')]) }}"  class="{{ $path[1] == $category->name ? 'bg-success text-light' : '' }}"><img src="{{ asset('storage/uploads/categories/'. $category->image) }}" alt="{{$category->name}}" draggable="false">{{$category->name}}</a>
                            </li>
                        @endforeach
                    </ul>
                    </div>
        </nav>
    </div>
</div>
<!-- codigo para el scroll de las categorias -->
<script>
  const container = document.getElementById('scrollContainer');

let isDragging = false;
let startX;
let scrollLeft;

// Evento de mouse down
container.addEventListener('mousedown', (e) => {
    isDragging = true;
    container.classList.add('active');
    startX = e.pageX - container.offsetLeft; // Punto de partida relativo al contenedor
    scrollLeft = container.scrollLeft;      // Desplazamiento actual
});

// Evento de mouse move
container.addEventListener('mousemove', (e) => {
    if (!isDragging) return; // Si no está arrastrando, no hacer nada
    e.preventDefault(); // Evitar selección de texto mientras arrastras
    const x = e.pageX - container.offsetLeft; // Posición actual
    const walk = (x - startX) * 2; // Distancia movida, ajustada para mayor sensibilidad
    container.scrollLeft = scrollLeft - walk;
});

// Evento de mouse up / mouse leave
['mouseup', 'mouseleave'].forEach(event => {
    container.addEventListener(event, () => {
        isDragging = false;
    });
});

// //arrar de imagenes de categorias
// const images = {
//     'Bebidas': `{{ asset('images/bebidas_cat.png') }}`,
//     'Brasas': `{{ asset('images/brasas_cat.png') }}`,
//     'Comida rápida': `{{ asset('images/comida_rapida_cat.png') }}`,
//     'Pizzas': `{{ asset('images/pizzas_cat.png') }}`,
//     'Makis': `{{ asset('images/makis_cat.png') }}`,
//     'Ensaladas': `{{ asset('images/ensaladas_cat.png') }}`,
//     'Salmones': `{{ asset('images/salmones_cat.png') }}`,
//     'Hamburguesas': `{{ asset('images/hamburguesa_cat.png') }}`,
//     'Caldos': `{{ asset('images/caldos_cat.png') }}`,
// };
// // console.log(images);

// //mostar las imagenes del array images dentro de una etiqueta img que esta dentro de un li 
// const lis = document.querySelectorAll('.menu li a');
// lis.forEach((li, index) => {
//     const category = li.textContent.trim();
//     // console.log(category);
//     const img = document.createElement('img');
//     img.src = images[category];
//     // console.log(img.src);
//     img.style.width = '75px';
//     img.style.height = 'auto';
//     img.draggable = false;
//     li.prepend(img);
// });

</script>