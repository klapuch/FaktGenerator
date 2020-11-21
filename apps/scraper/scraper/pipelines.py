import os
import scrapy
import csv
from typing import Union, Any, Dict


class CsvFilePipeline:
    def process_item(self, item: Union[Dict[Any, Any], scrapy.Item], spider: scrapy.Spider) -> Union[Dict[Any, Any], scrapy.Item]:
        if not os.path.exists(spider.FILENAME):
            with open(spider.FILENAME, 'a+') as file:
                writer = csv.writer(file)
                writer.writerow(['text'])
        with open(spider.FILENAME, 'a+') as file:
            writer = csv.writer(file)
            writer.writerow([item['text']])
        return item
