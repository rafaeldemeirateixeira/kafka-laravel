<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Enqueue\RdKafka\RdKafkaConnectionFactory;
use App\Models\Contato;

class ConsumerPDF extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kafka:consumer-pdf {topic}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Consumidor de PDF';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $topic = $this->argument('topic');

        $connectionFactory = new RdKafkaConnectionFactory([
            'global' => [
                'group.id' => 'laravel',
                'metadata.broker.list' => 'kafka:9092',
                'enable.auto.commit' => 'true',
            ],
            'topic' => [
                'auto.offset.reset' => 'beginning',
            ],
        ]);

        $context = $connectionFactory->createContext();
        $fooQueue = $context->createQueue($topic);
        $consumer = $context->createConsumer($fooQueue);

        $a = 0;
        while ($a <= 1000) {
            $message = $consumer->receive();

            logger('Consumer message', [$message]);

            if ($this->createContato($message->getBody())) {
                $consumer->acknowledge($message);
            } else {
                //$consumer->reject($message);
            }

            $a++;
        }
    }

    public function createContato($message)
    {
        try {
            $contato = new Contato();
            $object = json_decode($message);
    
            $contato->name = $object->name;
            $contato->email = $object->email;
            $contato->phone = $object->phone;
            $contato->description = $object->description;
    
            $contato->save();
    
            return true;
        } catch (\Exception $e) {
            logger()->error('Consumer', [$e->getMessage(), $message]);
            return false;
        }
    }
}
