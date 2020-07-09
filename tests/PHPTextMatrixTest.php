<?php

/*
 * This file is part of the PHP Text Matrix Component.
 *
 * Copyright Adamo Aerendir Crespi 2016-2020.
 *
 * See the LICENSE for more details.
 *
 * @author    Adamo Aerendir Crespi <hello@aerendir.me>
 * @copyright Copyright (C) 2012 - 2020 Aerendir. All rights reserved.
 * @license   MIT License
 */

namespace SerendipityHQ\Component\PHPTextMatrix\Tests;

use PHPUnit\Framework\TestCase;
use SerendipityHQ\Component\PHPTextMatrix\PHPTextMatrix;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

/**
 * @author Adamo "Aerendir" Crespi <hello@aerendir.me>
 */
final class PHPTextMatrixTest extends TestCase
{
    /** @var array */
    private $data;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->data = [
            [
                'a' => 'a1',
                'b' => 'b1',
                'c' => 'c1',
            ],
            [
                'a' => 'a2',
                'b' => 'b2',
                'c' => 'c2',
            ],
            [
                'a' => 'a3',
                'b' => 'b3',
                'c' => 'c3',
            ],
        ];
    }

    public function testTextMatrix(): void
    {
        $expected = <<<'EOF'
+--+--+--+
|a1|b1|c1|
+--+--+--+
|a2|b2|c2|
+--+--+--+
|a3|b3|c3|
+--+--+--+

EOF;

        $textMatrix = new PHPTextMatrix($this->data);
        $result     = $textMatrix->render();

        $this::assertEquals($expected, $result);
        $this::assertEquals(10, $textMatrix->getTableWidth());
    }

    public function testValidationInterceptsMismatchingColumns(): void
    {
        // Remove a column from a row
        unset($this->data[1]['b']);

        $textMatrix = new PHPTextMatrix($this->data);
        $result     = $textMatrix->render();

        $this::assertEmpty($result);

        $errors = $textMatrix->getErrors();

        $this::assertCount(1, $errors);
        $this::assertStringContainsString('The number of columns mismatches', $errors[0]);
    }

    public function testValidationInterceptsEmptyMatrix(): void
    {
        // Remove a column from a row
        $this->data = [];

        $textMatrix = new PHPTextMatrix($this->data);
        $result     = $textMatrix->render();

        $this::assertEmpty($result);

        $errors = $textMatrix->getErrors();

        $this::assertCount(1, $errors);
        $this::assertStringContainsString('There are no rows in the table', $errors[0]);
    }

    public function testHeaderDrawing(): void
    {
        $header = [
            'a' => 'Column A',
            'b' => 'Column B',
            'c' => 'Column C',
        ];

        $options = [
            PHPTextMatrix::HAS_HEADER => true,
        ];

        \array_unshift($this->data, $header);

        $expected = <<<'EOF'
#========#========#========#
#Column A#Column B#Column C#
#========#========#========#
|a1      |b1      |c1      |
+--------+--------+--------+
|a2      |b2      |c2      |
+--------+--------+--------+
|a3      |b3      |c3      |
+--------+--------+--------+

EOF;

        $textMatrix = new PHPTextMatrix($this->data);
        $result     = $textMatrix->render($options);

        $this::assertSame($expected, $result);
        $this::assertEquals(28, $textMatrix->getTableWidth());
    }

    public function testHeaderDrawingCanRemoveHeaderTopSeparator(): void
    {
        $header = [
            'a' => 'Column A',
            'b' => 'Column B',
            'c' => 'Column C',
        ];

        \array_unshift($this->data, $header);

        $options = [
            PHPTextMatrix::HAS_HEADER              => true,
            PHPTextMatrix::SHOW_HEAD_TOP_SEPARATOR => false,
        ];

        $expected = <<<'EOF'
#Column A#Column B#Column C#
#========#========#========#
|a1      |b1      |c1      |
+--------+--------+--------+
|a2      |b2      |c2      |
+--------+--------+--------+
|a3      |b3      |c3      |
+--------+--------+--------+

EOF;

        $textMatrix = new PHPTextMatrix($this->data);
        $result     = $textMatrix->render($options);

        $this::assertSame($expected, $result);
        $this::assertEquals(28, $textMatrix->getTableWidth());
    }

    public function testPaddingAsInteger(): void
    {
        $options = [
            PHPTextMatrix::CELLS_PADDING => 1,
        ];

        $expected = <<<'EOF'
+----+----+----+
|    |    |    |
| a1 | b1 | c1 |
|    |    |    |
+----+----+----+
|    |    |    |
| a2 | b2 | c2 |
|    |    |    |
+----+----+----+
|    |    |    |
| a3 | b3 | c3 |
|    |    |    |
+----+----+----+

EOF;

        $textMatrix = new PHPTextMatrix($this->data);
        $result     = $textMatrix->render($options);

        $this::assertSame($expected, $result);
        $this::assertEquals(16, $textMatrix->getTableWidth());
    }

    public function testPaddingAsArrayWithOneSetting(): void
    {
        $options = [
            PHPTextMatrix::CELLS_PADDING => [1],
        ];

        $expected = <<<'EOF'
+----+----+----+
|    |    |    |
| a1 | b1 | c1 |
|    |    |    |
+----+----+----+
|    |    |    |
| a2 | b2 | c2 |
|    |    |    |
+----+----+----+
|    |    |    |
| a3 | b3 | c3 |
|    |    |    |
+----+----+----+

EOF;

        $textMatrix = new PHPTextMatrix($this->data);
        $result     = $textMatrix->render($options);

        $this::assertSame($expected, $result);
        $this::assertEquals(16, $textMatrix->getTableWidth());
    }

    public function testPaddingAsArrayWithTwoSettings(): void
    {
        $options = [
            PHPTextMatrix::CELLS_PADDING => [1, 2],
        ];

        $expected = <<<'EOF'
+------+------+------+
|      |      |      |
|  a1  |  b1  |  c1  |
|      |      |      |
+------+------+------+
|      |      |      |
|  a2  |  b2  |  c2  |
|      |      |      |
+------+------+------+
|      |      |      |
|  a3  |  b3  |  c3  |
|      |      |      |
+------+------+------+

EOF;

        $textMatrix = new PHPTextMatrix($this->data);
        $result     = $textMatrix->render($options);

        $this::assertSame($expected, $result);
        $this::assertEquals(22, $textMatrix->getTableWidth());
    }

    public function testPaddingAsArrayWithThreeSettings(): void
    {
        $options = [
            PHPTextMatrix::CELLS_PADDING => [1, 2, 3],
        ];

        $expected = <<<'EOF'
+------+------+------+
|      |      |      |
|  a1  |  b1  |  c1  |
|      |      |      |
|      |      |      |
|      |      |      |
+------+------+------+
|      |      |      |
|  a2  |  b2  |  c2  |
|      |      |      |
|      |      |      |
|      |      |      |
+------+------+------+
|      |      |      |
|  a3  |  b3  |  c3  |
|      |      |      |
|      |      |      |
|      |      |      |
+------+------+------+

EOF;

        $textMatrix = new PHPTextMatrix($this->data);
        $result     = $textMatrix->render($options);

        $this::assertSame($expected, $result);
        $this::assertEquals(22, $textMatrix->getTableWidth());
    }

    public function testPaddingAsArrayWithFourSettings(): void
    {
        $options = [
            PHPTextMatrix::CELLS_PADDING => [1, 2, 3, 4],
        ];

        $expected = <<<'EOF'
+--------+--------+--------+
|        |        |        |
|    a1  |    b1  |    c1  |
|        |        |        |
|        |        |        |
|        |        |        |
+--------+--------+--------+
|        |        |        |
|    a2  |    b2  |    c2  |
|        |        |        |
|        |        |        |
|        |        |        |
+--------+--------+--------+
|        |        |        |
|    a3  |    b3  |    c3  |
|        |        |        |
|        |        |        |
|        |        |        |
+--------+--------+--------+

EOF;

        $textMatrix = new PHPTextMatrix($this->data);
        $result     = $textMatrix->render($options);

        $this::assertSame($expected, $result);
        $this::assertEquals(28, $textMatrix->getTableWidth());
    }

    public function testPaddingAcceptsMaxFourOptions(): void
    {
        $options = [
            PHPTextMatrix::CELLS_PADDING => [1, 2, 3, 4, 5],
        ];

        $textMatrix = new PHPTextMatrix($this->data);

        $this->expectException(InvalidOptionsException::class);
        $textMatrix->render($options);
    }

    public function testColumnMaxWidth(): void
    {
        $data = [
            [
                'a' => 'a1',
                'b' => 'longer than 11',
                'c' => 'c1',
            ],
            [
                'a' => 'a2',
                'b' => 'longer than 11',
                'c' => 'c2',
            ],
            [
                'a' => 'a3',
                'b' => 'longer than 11',
                'c' => 'c3',
            ],
        ];

        $options = [
            PHPTextMatrix::COLUMNS => [
                'b' => [
                    PHPTextMatrix::MAX_WIDTH => 11,
                ],
            ],
        ];

        $expected = <<<'EOF'
+--+-----------+--+
|a1|longer than|c1|
|  |11         |  |
+--+-----------+--+
|a2|longer than|c2|
|  |11         |  |
+--+-----------+--+
|a3|longer than|c3|
|  |11         |  |
+--+-----------+--+

EOF;

        $textMatrix = new PHPTextMatrix($data);
        $result     = $textMatrix->render($options);

        $this::assertSame($expected, $result);
        $this::assertEquals(19, $textMatrix->getTableWidth());
    }

    public function testCutDefaultsToFalse(): void
    {
        $data = [
            [
                'a' => 'a1',
                'b' => 'longer than 11',
                'c' => 'c1',
            ],
            [
                'a' => 'a2',
                'b' => 'longer than 11',
                'c' => 'c2',
            ],
            [
                'a' => 'a3',
                'b' => 'longer than 11',
                'c' => 'c3',
            ],
        ];

        $options = [
            PHPTextMatrix::COLUMNS => [
                'b' => [
                    // This is shorter than "longer" that is 6 characters long
                    PHPTextMatrix::MAX_WIDTH => 5,
                ],
            ],
        ];

        $expected = <<<'EOF'
+--+------+--+
|a1|longer|c1|
|  |than  |  |
|  |11    |  |
+--+------+--+
|a2|longer|c2|
|  |than  |  |
|  |11    |  |
+--+------+--+
|a3|longer|c3|
|  |than  |  |
|  |11    |  |
+--+------+--+

EOF;

        $textMatrix = new PHPTextMatrix($data);
        $result     = $textMatrix->render($options);

        $this::assertSame($expected, $result);
        $this::assertEquals(14, $textMatrix->getTableWidth());
    }

    public function testCutToTrue(): void
    {
        $data = [
            [
                'a' => 'a1',
                'b' => 'longer than 11',
                'c' => 'c1',
            ],
            [
                'a' => 'a2',
                'b' => 'longer than 11',
                'c' => 'c2',
            ],
            [
                'a' => 'a3',
                'b' => 'longer than 11',
                'c' => 'c3',
            ],
        ];

        $options = [
            PHPTextMatrix::COLUMNS => [
                'b' => [
                    // This is shorter than "longer" that is 6 characters long
                    PHPTextMatrix::MAX_WIDTH => 5,
                    PHPTextMatrix::CUT       => true,
                ],
            ],
        ];

        $expected = <<<'EOF'
+--+-----+--+
|a1|longe|c1|
|  |r    |  |
|  |than |  |
|  |11   |  |
+--+-----+--+
|a2|longe|c2|
|  |r    |  |
|  |than |  |
|  |11   |  |
+--+-----+--+
|a3|longe|c3|
|  |r    |  |
|  |than |  |
|  |11   |  |
+--+-----+--+

EOF;

        $textMatrix = new PHPTextMatrix($data);
        $result     = $textMatrix->render($options);

        $this::assertSame($expected, $result);
        $this::assertEquals(13, $textMatrix->getTableWidth());
    }

    public function testAlignRight(): void
    {
        $data = [
            [
                'a' => 'a1',
                'b' => 'longer than 11',
                'c' => 'c1',
            ],
            [
                'a' => 'a2',
                'b' => 'longer than 11',
                'c' => 'c2',
            ],
            [
                'a' => 'a3',
                'b' => 'longer than 11',
                'c' => 'c3',
            ],
        ];

        $options = [
            PHPTextMatrix::COLUMNS => [
                'b' => [
                    PHPTextMatrix::MAX_WIDTH => 5,
                    PHPTextMatrix::ALIGN     => PHPTextMatrix::ALIGN_RIGHT,
                ],
            ],
        ];

        $expected = <<<'EOF'
+--+------+--+
|a1|longer|c1|
|  |  than|  |
|  |    11|  |
+--+------+--+
|a2|longer|c2|
|  |  than|  |
|  |    11|  |
+--+------+--+
|a3|longer|c3|
|  |  than|  |
|  |    11|  |
+--+------+--+

EOF;

        $textMatrix = new PHPTextMatrix($data);
        $result     = $textMatrix->render($options);

        $this::assertSame($expected, $result);
        $this::assertEquals(14, $textMatrix->getTableWidth());
    }

    public function testCustomSeparatorsAndMinWidth(): void
    {
        $data = [
            [
                'quantity'    => 'Quantity',
                'description' => 'Description',
                'price'       => 'Price',
            ],
            [
                'quantity'    => '1 month',
                'description' => 'TrustBack.Me:       Base plan' . PHP_EOL . 'From Sep 26 2016 to Oct 26 2016.',
                'price'       => '$29.00',
            ],
            [
                'quantity'    => '',
                'description' => 'Credit applied',
                'price'       => '-$29.00',
            ],
        ];

        $options = [
            PHPTextMatrix::HAS_HEADER              => true,
            PHPTextMatrix::CELLS_PADDING           => [0, 3],
            PHPTextMatrix::SEP_HEAD_V              => ' ',
            PHPTextMatrix::SEP_HEAD_X              => ' ',
            PHPTextMatrix::SEP_HEAD_H              => '-',
            PHPTextMatrix::SEP_V                   => ' ',
            PHPTextMatrix::SEP_X                   => ' ',
            PHPTextMatrix::SEP_H                   => ' ',
            PHPTextMatrix::SHOW_HEAD_TOP_SEP       => false,
            PHPTextMatrix::COLUMNS                 => [
                'description' => [
                    PHPTextMatrix::MAX_WIDTH => 40,
                    // Equal to CSS word-break: break-all
                    PHPTextMatrix::CUT => true,
                ],
                'price' => [
                    PHPTextMatrix::ALIGN     => PHPTextMatrix::ALIGN_RIGHT,
                    PHPTextMatrix::MIN_WIDTH => 15,
                ],
            ],
        ];

        $expected = <<<'EOF'
    Quantity       Description                                      Price
 -------------- -------------------------------------- ---------------------
    1 month        TrustBack.Me: Base plan                         $29.00
                   From Sep 26 2016 to Oct 26 2016.

                   Credit applied                                 -$29.00


EOF;

        $textMatrix = new PHPTextMatrix($data);
        $result     = $textMatrix->render($options);

        $this::assertSame($expected, $result);
        $this::assertEquals(77, $textMatrix->getTableWidth());
    }
}
