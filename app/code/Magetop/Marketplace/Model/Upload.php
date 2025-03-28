<?php
/**
 * @author      Magetop Developer (Hau)
 * @package     Magento Multi Vendor Marketplace
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\Marketplace\Model;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\File\Uploader;
class Upload
{
    /**
     * uploader factory
     *
     * @var \Magento\Core\Model\File\UploaderFactory
     */
    protected $uploaderFactory;
    /**
     * constructor
     *
     * @param UploaderFactory $uploaderFactory
     */
    public function __construct(UploaderFactory $uploaderFactory)
    {
        $this->uploaderFactory = $uploaderFactory;
    }
    /**
     * upload file
     *
     * @param $input
     * @param $destinationFolder
     * @param $data
     * @return string
     * @throws \Magento\Framework\Model\Exception
     */
    public function uploadFileAndGetName($input, $destinationFolder, $data)
    {
        try {
            if (isset($data[$input]['delete'])) {
				if(isset($data[$input]['value']) && $data[$input]['value'] != '')
				{
					/* $placeStrImage =  strrpos($data[$input]['value'],'/');
					$placeStrImage++;
					$fileName = substr($data[$input]['value'],$placeStrImage);
					echo $destinationFolder.'/'.$fileName;
					if(is_file($destinationFolder.'/'.$filename))
					{
						echo "delete is ok";
						//@unlink($destinationFolder.'/'.$filename);
					} 
					/*if(is_file($destinationFolder.'/resized/'.$filename))
					@unlink($destinationFolder.'/resized/'.$filename); */
				}
				return '';
            } else {
                $uploader = $this->uploaderFactory->create(['fileId' => $input]);
				$ext = $uploader->getFileExtension();
				$newFileName = 'image-'.time().'.'.$ext;
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(false);
                //$uploader->setAllowCreateFolders(true);
                $result = $uploader->save($destinationFolder,$newFileName);
                return $result['file'];
            }
        } catch (\Exception $e) {
            if ($e->getCode() != Uploader::TMP_NAME_EMPTY) {
                throw new \Exception($e->getMessage());
            } else {
                if (isset($data[$input]['value'])) {
					$fileName = substr($data[$input]['value'],strrpos($data[$input]['value'],'/') + 1);
                    return $fileName;
                }
            }
        }
        return '';
    }
}