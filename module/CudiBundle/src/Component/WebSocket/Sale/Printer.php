<?php
/**
 * Litus is a project by a group of students from the K.U.Leuven. The goal is to create
 * various applications to support the IT needs of student unions.
 *
 * @author Niels Avonds <niels.avonds@litus.cc>
 * @author Karsten Daemen <karsten.daemen@litus.cc>
 * @author Bram Gotink <bram.gotink@litus.cc>
 * @author Pieter Maene <pieter.maene@litus.cc>
 * @author Kristof Mariën <kristof.marien@litus.cc>
 *
 * @license http://litus.cc/LICENSE
 */

namespace CudiBundle\Component\WebSocket\Sale;

use Doctrine\ORM\EntityManager;

class Printer {
    public static function queuePrint(EntityManager $entityManager, $printer, $identification, $fullName, $barcode, $queueNumber, $totalPrice, $articles)
    {
        $data = self::_createData($identification, $fullName, $barcode, $queueNumber, $totalPrice, $articles);
        $data->type = 1;
        self::_print($entityManager, $printer, $data);
    }

    public static function collectPrint(EntityManager $entityManager, $printer, $identification, $fullName, $barcode, $queueNumber, $totalPrice, $articles)
    {
        $data = self::_createData($identification, $fullName, $barcode, $queueNumber, $totalPrice, $articles);
        $data->type = 2;
        self::_print($entityManager, $printer, $data);
    }

    public static function salePrint(EntityManager $entityManager, $printer, $identification, $fullName, $barcode, $queueNumber, $totalPrice, $articles)
    {
        $data = self::_createData($identification, $fullName, $barcode, $queueNumber, $totalPrice, $articles);
        $data->type = 3;
        self::_print($entityManager, $printer, $data);
    }

    private static function _createData($identification, $fullName, $barcode, $queueNumber, $totalPrice, $articles)
    {
        $sort = array();
        foreach($articles as $article) {
            $sort[] = $article['barcode'];
        }
        array_multisort($articles, $sort);

        return (object) array(
            'id' => $identification,
            'barcode' => $barcode,
            'name' => $fullName,
            'queuenumber' => $queueNumber,
            'totalAmount' => $totalPrice,
            'items' => $articles,
        );
    }

    private static function _print(EntityManager $entityManager, $printer, $data)
    {
        $printers = unserialize(
            $entityManager->getRepository('CommonBundle\Entity\General\Config')
                ->getConfigValue('cudi.printers')
        );

        if (!isset($printers[$printer]))
            return;

        $data = json_encode(
            (object) array(
                'command' => 'PRINT',
                'id' => $printers[$printer],
                'ticket' => $data,
                'key' => $entityManager->getRepository('CommonBundle\Entity\General\Config')
                    ->getConfigValue('cudi.queue_socket_key'),
            )
        );

        $socket = socket_create(AF_INET, SOCK_STREAM, getprotobyname('tcp'));
        socket_connect(
            $socket,
            $entityManager->getRepository('CommonBundle\Entity\General\Config')
                ->getConfigValue('cudi.print_socket_address'),
            $entityManager->getRepository('CommonBundle\Entity\General\Config')
                ->getConfigValue('cudi.print_socket_port')
        );
        socket_write($socket, $data);
        socket_close($socket);
    }
}
