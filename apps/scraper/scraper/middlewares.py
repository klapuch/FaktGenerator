import random
import scrapy
from scrapy.utils.project import get_project_settings
from typing import Any


class RandomUserAgentMiddleware:
    def process_request(self, request: Any, spider: scrapy.Spider) -> None:
        settings = get_project_settings()
        request.headers.setdefault('User-Agent', random.choice(settings['USER_AGENTS']))
        return None
