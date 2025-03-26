<?php
/**
 * Copyright © 2015  (magetop99@gmail.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * 
 */

namespace Magetop\Blog\Controller\Adminhtml\Post;

use Magetop\Blog\Model\Post;
/**
 * Blog post save controller
 */
class Save extends \Magetop\Blog\Controller\Adminhtml\Post
{
    /**
     * Before model save
     * @param  \Magetop\Blog\Model\Post $model
     * @param  \Magento\Framework\App\Request\Http $request
     * @return void
     */
    protected function _beforeSave($model, $request)
    {
        /* prepare publish date */
        $dateFilter = $this->_objectManager->create('Magento\Framework\Stdlib\DateTime\Filter\Date');
        $data = $model->getData();

        $inputFilter = new \Zend_Filter_Input(
            ['publish_time' => $dateFilter],
            [],
            $data
        );
        $data = $inputFilter->getUnescaped();
        $model->setData($data);

        /* prepare relative links */
        if ($links = $request->getPost('links')) {

            $jsHelper = $this->_objectManager->create('Magento\Backend\Helper\Js');

            $links = is_array($links) ? $links : [];
            $linkTypes = ['relatedposts', 'relatedproducts'];
            foreach ($linkTypes as $type) {

                if (isset($links[$type])) {
                    $links[$type] = $jsHelper->decodeGridSerializedInput($links[$type]);

                    $model->setData($type.'_links', $links[$type]);
                }
            }
        }

        /* prepare featured image */
        $imageField = 'featured_img';
        $fileSystem = $this->_objectManager->create('Magento\Framework\Filesystem');
        $mediaDirectory = $fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);

        if (!empty($_FILES['post']['name'][$imageField])) {

            $uploader = $this->_objectManager->create('Magento\MediaStorage\Model\File\UploaderFactory');
            $uploader = $uploader->create(['fileId' => 'post[featured_img]']);
            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);

            $result = $uploader->save(
                $mediaDirectory->getAbsolutePath(Post::BASE_MEDIA_PATH)
            );
            $model->setData($imageField, Post::BASE_MEDIA_PATH . $result['file']);
        } else {
            if (isset($data[$imageField]) && isset($data[$imageField]['value'])) {
                if (isset($data[$imageField]['delete'])) {
                    unlink($mediaDirectory->getAbsolutePath() . $data[$imageField]['value']);
                    $model->setData($imageField, '');
                } else {
                    $model->unsetData($imageField);
                }
            }
        }


    }

}
