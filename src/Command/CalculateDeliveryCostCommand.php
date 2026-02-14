<?php

declare(strict_types=1);

namespace App\Command;

use App\Factory\OrderFactory;
use App\Service\CalculateDeliveryCost;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
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
            ->addOption('countryCode', 'cc', InputOption::VALUE_OPTIONAL)
            ->addOption('createdAt', 'ca', InputOption::VALUE_OPTIONAL);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $weight = $input->getOption('weight');
        $totalPrice = $input->getOption('totalPrice');
        $countryCode = $input->getOption('countryCode');
        $createdAt = $input->getOption('createdAt');

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
        if ($createdAt !== null) {
            $orderJson['createdAt'] = $createdAt;
        }

        $io->section('Usage');
        $io->text([
            'Order data is loaded from order.json. You can override selected fields with options:',
            'Order items do nothing, they are not used in the calculation :(',
        ]);
        $io->listing([
            '<options=bold>--weight</>, <options=bold>-w</>  – weight in kg)',
            '<options=bold>--totalPrice</>, <options=bold>-p</>  – cart total in PLN)',
            '<options=bold>--countryCode</>, <options=bold>-cc</>  – country code, e.g. PL, DE, USA)',
            '<options=bold>--createdAt</>, <options=bold>-ca</>  – order date and time (YYYY-MM-DDTHH:MM:SSZ)',
        ]);
        $io->text('Example: <comment>php bin/console app:calculate-delivery-cost --countryCode=USA --totalPrice=500 --createdAt=2026-02-12T16:30:00Z</comment>');
        $io->newLine();

        $io->section('JSON content');
        $io->writeln(json_encode($orderJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        try {
            $order = $this->orderFactory->create($orderJson);

            $io->section('Order summary:');
            $io->listing([
                'Weight: ' . $order->getWeight()->getValue() . ' kg',
                'Total price: ' . $order->getTotalPrice()->getFormatted(),
                'Country: ' . $order->getCountryCode(),
                'Created at: ' . $order->getCreatedAt()->format('Y-m-d H:i:s')
                    . ' (day of week: ' . $this->getDayOfWeekName((int) $order->getCreatedAt()->format('N')) . ')',
            ]);

            $deliveryCost = $this->calculateDeliveryCost->execute($order);
            $costFormatted = $deliveryCost->getValue()->getValue() . ' ' . $deliveryCost->getCurrencyCode();

            $io->success('Calculation result: ' . $costFormatted);

            return Command::SUCCESS;
        } catch (Throwable $throwable) {
            $io->error([
                'Error: ' . $throwable->getMessage(),
                get_class($throwable) . ' in ' . $throwable->getFile() . ':' . $throwable->getLine(),
            ]);

            return Command::FAILURE;
        }
    }

    private function getDayOfWeekName(int $dayOfWeek): string
    {
        return match ($dayOfWeek) {
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
            7 => 'Sunday',
            default => throw new \InvalidArgumentException('Invalid day of week: ' . $dayOfWeek),
        };
    }
}
