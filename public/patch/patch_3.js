require('HttpManager,NSMutableDictionary,NSNumber,NSByteCountFormatter,NSString,USAlertView,UIApplication,NSURL,USPhoto,USShoppingManager,USUMFeedbackViewController,USFeedbackViewController,HLTool');
defineClass('USUploadViewController', {
    startUploadAction: function() {
        var shoppingManager = self.valueForKey("_shoppingManager");
        if (HttpManager.defaultManager().networkStatus() == 1) {
            var lengthMaper = NSMutableDictionary.dictionary();
            var cloudMaper = shoppingManager.cloudMaper();
            var uploadingImages = shoppingManager.uploadingImages().toJS();
            for (var index in uploadingImages) {
                var obj = uploadingImages[index];
                var identifier = obj.identifier();
                if (!cloudMaper || !cloudMaper.objectForKey(identifier)) {
                var length =  obj.length();
                  lengthMaper.setObject_forKey(NSString.stringWithFormat("%@",length), identifier);
                }
            }
            var totalLength = 0;
            var allValues = lengthMaper.allValues().toJS();
            for (var index in allValues) {
                var obj = allValues[index];
                totalLength += parseFloat(obj);
            }
            if (totalLength) {
                shoppingManager.setIsFailed(1);
                self.updateDisplay();
                var message = NSByteCountFormatter.stringFromByteCount_countStyle(totalLength, 3);
                message = NSString.stringWithFormat("本次上传照片文件大小为%@，您现在处于非wifi环境，确定要继续上传吗？", message);
                var alertView = USAlertView.initWithTitle_message_cancelButtonTitle_otherButtonTitles(null, message, "继续上传", "去打开WIFI", null);
                alertView.showWithCompletionBlock(block('NSInteger', function(buttonIndex) {
                   if(buttonIndex == 0) {
                        shoppingManager.setIsFailed(0);
                        shoppingManager.startUpload();
                    } else if (buttonIndex == 1) {
                        UIApplication.sharedApplication().openURL(NSURL.URLWithString("prefs:root=WIFI"));
                    }
                }));
            self.setValue_forKey(alertView, "_alertView");
        } else {
            shoppingManager.setIsFailed(0);
            shoppingManager.startUpload();
        }
    } else if (!shoppingManager.isUploading()) {
        shoppingManager.setIsFailed(0);
        shoppingManager.startUpload();
    }
}, feedbackButtonAction: function(sender) {
    if (HLTool.baichuanFeedback()) {
        var feedVC = USFeedbackViewController.viewController();
        self.navigationController().pushViewController_animated(feedVC, 1);
    } else {
        var feedVC = USUMFeedbackViewController.viewController();
        self.navigationController().pushViewController_animated(feedVC, 1);
    }
},
});

require('PHImageRequestOptions,PHImageManager');
defineClass('PHAsset', {
    originalImageData: function() {
        var data;
        var imageRequestOptions = PHImageRequestOptions.alloc().init();
        imageRequestOptions.setSynchronous(1);
        imageRequestOptions.setNetworkAccessAllowed(1);
        PHImageManager.defaultManager().requestImageDataForAsset_options_resultHandler(self, imageRequestOptions, block('NSData*,NSString*,UIImageOrientation,NSDictionary*', function(imageData, dataUTI, orientation, info) {
                data = imageData;
        }));
    return data;
},
});