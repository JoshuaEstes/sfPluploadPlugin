<?php

/**
 * sfPlupload actions.
 *
 * @package    video.iostudio.com
 * @subpackage sfPlupload
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class sfPluploadActions extends sfActions
{

  /**
   * Process a file upload
   *
   * @param sfWebRequest $request
   */
  public function executeUpload(sfWebRequest $request)
  {
    $this->setTemplate(false);
    
    set_time_limit(15 * 60);

    $targetDir = sfConfig::get('sf_upload_dir');

    $chunk = $request->getParameter('chunk', 0);
    $chunks = $request->getParameter('chunks', 0);
    $fileName = $request->getParameter('name','');

    $fileName = preg_replace('/[^\w\._]+/', '', $fileName);

    // Make sure the fileName is unique but only if chunking is disabled
    if($chunks < 2 && file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName))
    {
      $ext = strrpos($fileName, '.');
      $fileName_a = substr($fileName, 0, $ext);
      $fileName_b = substr($fileName, $ext);

      $count = 1;
      while(file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName_a . '_' . $count . $fileName_b))
        $count++;

      $fileName = $fileName_a . '_' . $count . $fileName_b;
    }

    // Look for the content type header
    $pathInfo = $request->getPathInfoArray();
    $contentType = '';
    if(isset($pathInfo["CONTENT_TYPE"]))
    {
      $contentType = $pathInfo["CONTENT_TYPE"];
    }
    elseif(isset($pathInfo["HTTP_CONTENT_TYPE"]))
    {
      $contentType = $pathInfo["HTTP_CONTENT_TYPE"];
    }

    $files = $request->getFiles();
    $files = $files['file'];

    // Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
    if(strpos($contentType, "multipart") !== false)
    {
      if (isset($files['error']) && $files['error'])
      {
        echo sprintf('{"jsonrpc": "2.0", "error" : { "message": "%s" }}',$files['error']);
      }
      if(isset($files['tmp_name']) && is_uploaded_file($files['tmp_name']))
      {
        // Open temp file
        $out = fopen($targetDir . DIRECTORY_SEPARATOR . $fileName, $chunk == 0 ? "wb" : "ab");
        if($out)
        {
          // Read binary input stream and append it to temp file
          $in = fopen($files['tmp_name'], "rb");

          if($in)
          {
            while($buff = fread($in, 4096))
            {
              fwrite($out, $buff);
            }
          }
          else
          {
            echo '{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}';
          }
          fclose($in);
          fclose($out);
          unlink($files['tmp_name']);
        }
        else
        {
          echo '{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}';
        }
      }
      else
      {
        echo '{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}';
      }
    }
    else
    {
      // Open temp file
      $out = fopen($targetDir . DIRECTORY_SEPARATOR . $fileName, $chunk == 0 ? "wb" : "ab");
      if($out)
      {
        // Read binary input stream and append it to temp file
        $in = fopen("php://input", "rb");

        if($in)
        {
          while($buff = fread($in, 4096))
            fwrite($out, $buff);
        }
        else
        {
          echo '{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}';
        }

        fclose($in);
        fclose($out);
      }
      else
      {
        echo '{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}';
      }
    }

    if ($chunks == ($chunk + 1))
    {
      echo '{"jsonrpc" : "2.0", "result" : "complete", "id" : "id"}';
    }

    echo '{"jsonrpc" : "2.0", "result" : null, "id" : "id"}';
    
    return sfView::NONE;
  }

}
