from typing import Mapping, Sequence

from ..core import Markdown

class Extension:
    config: Mapping[str, str] = ...
    def __init__(self, **kwargs: Mapping[str, str]) -> None: ...
    def getConfig(self, key: str, default: str = ...) -> str: ...
    def getConfigs(self) -> Mapping[str, str]: ...
    def getConfigInfo(self) -> Sequence[Mapping[str, str]]: ...
    def setConfig(self, key: str, value: str) -> None: ...
    def setConfigs(self, items: Mapping[str, str]) -> None: ...
    def extendMarkdown(self, md: Markdown) -> None: ...
