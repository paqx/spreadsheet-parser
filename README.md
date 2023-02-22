# Description

A simple CLI spreadsheet parser powered by Yii2 and PhpSpreadsheet.

# Usage

    php yii parser/run [--docType] [--sheetName] [--range] [--ctrlColumn] [--ignore] [--mode] [--mapping] [--model] <filename>

**--docType** Type of the spreadsheet to be parsed: Xlsx, Ods, etc.

**--sheetName** Name of the active sheet.

**--range** Range of cells to be parsed, e.g. A3:N3, where A3 is the upper left corner and N3 is the bottom right corner of the area.

**--ctrlColumn** This is a so-called control column. It is used to: determine if the current row defines a new category; check if the current row already exists in the database.

**--ignore** The option is used to ignore certain rows. A list of comma separated pairs of values should be passed, e.g. "A:,A:Total" means that if a cell in the A column is either empty or equals to "Total", the row must be skipped.

**--mode** The option can be used to dry-run the parser.

**--mapping** The option provides additional mapping of the spreadsheet columns to the database table, e.g. A:product says that the A column of the spreadsheet should be mapped to the product columns of the database table.

**--model** The name of the model that should be used to store data in the database.

## Example

    --docType=Ods --sheetName=MA --range=A3:N108 --ctrlColumn=A --ignore="A:,A:Total" --mapping="A:product" --model=Budget assets/budget.ods
