interface ProductSlide {
    init($element):void
}

declare let slide:ProductSlide;

declare module "ekyna-product/cms/product-slide" {
    export = slide;
}
