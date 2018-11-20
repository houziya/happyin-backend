<?php
use PhpAmqpLib\Connection\AMQPStreamConnection;

return function ()
{
    return new AMQPStreamConnection(HI\Config\RabbitMQ\HOSTNAME, HI\Config\RabbitMQ\PORT, HI\Config\RabbitMQ\USERNAME, HI\Config\RabbitMQ\PASSWORD, HI\Config\RabbitMQ\VHOST);
}
?>
