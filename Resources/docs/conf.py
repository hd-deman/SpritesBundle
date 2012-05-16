import sys, os
extensions = []
templates_path = ['_templates']
source_suffix = '.rst'
master_doc = 'index'
project = u'SpritesBundle'
copyright = u'2012, Pierre Minnieur, David Buchmann'
version = '0.2'
release = '0.2.0'
language = 'php'
exclude_patterns = ['_build']
pygments_style = 'sphinx'
html_theme = 'default'
html_static_path = ['_static']
htmlhelp_basename = 'SpritesBundledoc'
latex_documents = [
  ('index', 'Sprites.tex', u'SpritesBundle Documentation',
   u'Pierre Minnieur, David Buchmann', 'manual'),
]
man_pages = [
    ('index', 'sprites', u'SpritesBundle Documentation',
     [u'Pierre Minnieur, David Buchmann'], 1)
]
