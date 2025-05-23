@php
    global $post;
@endphp
<style>
    .banner-page{
        background-image: url('{{App\Qlib\Qlib::get_thumbnail_link(@$post_id)}}');
    }
</style>
<section class="banner-page">
    <div class="container py-5">
        <div class="banner-page-content">
            <h2>{{@$post['post_title']}}</h2>
            <p>{{@$post['post_excerpt']}}</p>
        </div>
    </div>
    <div class="banner-page-overlay"></div>
</section>

  <!-- End Hero -->
