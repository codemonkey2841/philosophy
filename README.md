philosophy.php is based loosely on the Mouse Over from this particular [xkcd
comic](http://xkcd.com/903/), in which it states:

Wikipedia trivia: if you take any article, click on the first link in the
article text not in parentheses or italics, and then repeat, you will
eventually end up at "Philosophy".

This script scrapes Wikipedia, starting with the article Philosophy, and
determines what articles link to it. It recursively does this to each article
encountered (breadth-first with respect to the number of hops the current
article is away from the originating article) until each article has been
traversed. As each new article is encountered it is entered into a
pre-existing database so that the data can be analyzed at a later date.
