<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use App\Service\CalculateDeliveryCost;
use App\Factory\OrderFactory;

#[AsCommand(name: 'app:calculate-delivery-cost')]
class CalculateDeliveryCostCommand
{
    public function __construct(
        private readonly CalculateDeliveryCost $calculateDeliveryCost,
        private readonly OrderFactory $orderFactory
    ) {
    }

    public function __invoke(OutputInterface $output): int
    {
        $output->writeln('Start calculating delivery cost...');
        $output->writeln('');
    
        $orderData = file_get_contents(__DIR__ . '/order.json');
        $orderJson = json_decode($orderData, true, 512, JSON_THROW_ON_ERROR);
        $output->writeln('Order data loaded from JSON file:');
        $output->writeln(json_encode($orderJson, JSON_PRETTY_PRINT));


        $order = $this->orderFactory->create($orderJson);

        $output->writeln('Order object created from JSON data:');
        $output->writeln(print_r($order, true));

        $this->calculateDeliveryCost->execute($order);
        
        return Command::SUCCESS;
    }
}
