<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use App\Service\CalculateDeliveryCost;
use App\Factory\OrderFactory;
use Throwable;

#[AsCommand(name: 'app:calculate-delivery-cost')]
class CalculateDeliveryCostCommand extends Command
{
    public function __construct(
        private readonly CalculateDeliveryCost $calculateDeliveryCost,
        private readonly OrderFactory $orderFactory
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('weight', 'w', InputOption::VALUE_OPTIONAL)
            ->addOption('totalPrice', 'p', InputOption::VALUE_OPTIONAL)
            ->addOption('countryCode', 'cc', InputOption::VALUE_OPTIONAL);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $weight = $input->getOption('weight');
        $totalPrice = $input->getOption('totalPrice');
        $countryCode = $input->getOption('countryCode');

        $output->writeln('-- Start calculating delivery cost...');
    
        $orderData = file_get_contents(__DIR__ . '/order.json');
        $orderJson = json_decode($orderData, true, 512, JSON_THROW_ON_ERROR);

        if ($weight !== null) {
            $orderJson['weight'] = $weight;
        }
        if ($totalPrice !== null) {
            $orderJson['totalPrice'] = $totalPrice;
        }
        if ($countryCode !== null) {
            $orderJson['countryCode'] = $countryCode;
        }

        $output->writeln('-- Order data loaded from JSON file:');
        $output->writeln(json_encode($orderJson, JSON_PRETTY_PRINT));

        try {
            $order = $this->orderFactory->create($orderJson);

            $output->writeln('-- Order object created from JSON data:');
            $output->writeln('Weight: ' . $order->getWeight());
            $output->writeln('Total price: ' . $order->getTotalPrice()->getFormatted());
            $output->writeln('Country code: ' . $order->getCountryCode());
            $output->writeln('Created at: ' . $order->getCreatedAt()->format('Y-m-d H:i:s'));

            $deliveryCost = $this->calculateDeliveryCost->execute($order);
            $output->writeln('-- Calculated delivery cost: ' . $deliveryCost->getValue() . ' ' . $deliveryCost->getCurrencyCode());

            return Command::SUCCESS;
        } catch (Throwable $throwable) {
            $output->writeln('<error>Error: ' . $throwable->getMessage() . '</error>');
            $output->writeln('<error>' . get_class($throwable) . '</error>');
            $output->writeln('<error>' . $throwable->getFile() . ':' . $throwable->getLine() . '</error>');
            return Command::FAILURE;
        }
    }
}
