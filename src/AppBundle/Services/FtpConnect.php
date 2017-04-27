<?php

namespace AppBundle\Services;

use Symfony\Component\DependencyInjection\Container;

/**
 * Class Serializer
 * @package AppBundle\Services
 */
class FtpConnect
{

    /**
     * @var string|null
     */
    private $ftpAdress;

    /**
     * @var string|null
     */
    private $ftpLogin;

    /**
     * @var string|null
     */
    private $ftpPassword;

    /**
     * @var Container
     */
    private $container;

    /**
     * ApiConnector constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->ftpAdress = $container->getParameter("basic_api.ftp_path");
        $this->ftpLogin = $container->getParameter("basic_api.ftp_login");
        $this->ftpPassword = $container->getParameter("basic_api.ftp_password");
    }

    /**
     * Upload a file on ftp
     *
     * @param string $file
     * @param string $path
     * @return string : the path on ftp or empty
     */
    public function uploadFtpImage($file, $path)
    {
        $conn_id = ftp_connect($this->ftpAdress, 21, 10000);
        ftp_login($conn_id, $this->ftpLogin, $this->ftpPassword) or die("Cannot login");
        ftp_pasv($conn_id, true) or die("Cannot switch to passive mode");

        if (ftp_put($conn_id, 'uploads/'.$path.$file, 'uploads/'.$path.$file, FTP_BINARY)) {
            return '/'.$path.$file;
        }
        ftp_close($conn_id);

        return '';
    }

    /**
     * Delete a file from path on ftp
     *
     * @param string $name
     */
    public function deleteFtpImage($name)
    {
        $conn_id = ftp_connect($this->ftpAdress, 21, 10000);
        ftp_login($conn_id, $this->ftpLogin, $this->ftpPassword) or die("Cannot login");
        ftp_pasv($conn_id, true) or die("Cannot switch to passive mode");
        $list = ftp_nlist($conn_id, 'uploads'.dirname($name));
        if ($list != false && in_array('uploads'.$name, $list)) {
            ftp_delete($conn_id, 'uploads'.$name);
        }
        ftp_close($conn_id);
    }

}
