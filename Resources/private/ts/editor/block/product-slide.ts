/// <reference path="../../../../../../../../../assets/typings/index.d.ts" />

import {BasePlugin} from "ekyna-cms/editor/plugin/base-plugin";
import {BlockManager} from "ekyna-cms/editor/document-manager";
import * as ProductSlide from 'ekyna-product/cms/product-slide';

/**
 * ProductSlidePlugin
 */
class ProductSlidePlugin extends BasePlugin {
    edit() {
        super.edit();

        this.openModal(
            BlockManager.generateUrl(this.$element, 'admin_ekyna_cms_editor_block_edit'),
            (e:Ekyna.ModalResponseEvent) => {
                if (e.contentType == 'json') {
                    ProductSlide.init(this.$element);
                    e.modal.close();
                }
            });
    }
}

export = ProductSlidePlugin;

