<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Enqueue\RdKafka\RdKafkaConnectionFactory;

class ProducerPDF extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kafka:producer-pdf {topic}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Producer PDF';

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
                'group.id' => uniqid('', true),
                'metadata.broker.list' => 'kafka:9092',
                'enable.auto.commit' => 'false',
            ],
            'topic' => [
                'auto.offset.reset' => 'beginning',
            ],
        ]);
        
        $context = $connectionFactory->createContext();

        $a = 0;
        while ($a <= 10000) {
            $message = $context->createMessage(json_encode($this->getData()));
            $fooTopic = $context->createTopic($topic);
    
            $context->createProducer()->send($fooTopic, $message);

            $a++;
        }
    }

    public function getData()
    {
        $faker = \Faker\Factory::create();

        return [
            'name' => $faker->name(),
            'email' => $faker->unique()->email,
            'phone' => $faker->phoneNumber,
            'description' => $faker->paragraph
        ];
    }
}
