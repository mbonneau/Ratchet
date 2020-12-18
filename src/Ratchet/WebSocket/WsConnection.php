<?php
namespace Ratchet\WebSocket;
use Ratchet\AbstractConnectionDecorator;
use Ratchet\ConnectionInterface;
use Ratchet\RFC6455\Messaging\DataInterface;
use Ratchet\RFC6455\Messaging\Frame;
use Ratchet\RFC6455\Messaging\MessageBuffer;

/**
 * {@inheritdoc}
 * @property \StdClass $WebSocket
 */
class WsConnection extends AbstractConnectionDecorator {
    /** @var MessageBuffer */
    private $streamer;

    public function __construct(ConnectionInterface $conn, MessageBuffer $streamer) {
        parent::__construct($conn);
        $this->streamer = $streamer;
    }


    /**
     * {@inheritdoc}
     */
    public function send($msg) {
        if (!$this->WebSocket->closing) {
            if (!($msg instanceof DataInterface)) {
                $msg = new Frame($msg);
            }

            $this->streamer->sendFrame($msg);
        }

        return $this;
    }

    /**
     * @param int|\Ratchet\RFC6455\Messaging\DataInterface
     */
    public function close($code = 1000) {
        if ($this->WebSocket->closing) {
            return;
        }

        if ($code instanceof DataInterface) {
            $this->send($code);
        } else {
            $this->send(new Frame(pack('n', $code), true, Frame::OP_CLOSE));
        }

        $this->getConnection()->close();

        $this->WebSocket->closing = true;
    }
}
