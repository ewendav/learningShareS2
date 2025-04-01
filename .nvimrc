version 6.0
let s:cpo_save=&cpo
set cpo&vim
inoremap <silent> <C-S> <Cmd>w
inoremap <silent> <M-k> <Cmd>m .-2==gi
inoremap <silent> <M-j> <Cmd>m .+1==gi
inoremap <silent> <expr> <BS> v:lua.MiniPairs.bs()
cnoremap <expr> <BS> v:lua.MiniPairs.bs()
inoremap <C-W> u
inoremap <C-U> u
nnoremap <silent>  zz
nnoremap <silent>  h
nnoremap <silent> <NL> j
nnoremap <silent>  k
nnoremap <silent>  l
snoremap <silent>  <Cmd>w
nnoremap <silent>  <Cmd>w
xnoremap <silent>  <Cmd>w
nnoremap <silent>  zz
nmap  d
tnoremap <silent>  <Cmd>close
nnoremap  xt <Cmd>Trouble todo toggle
nnoremap  xT <Cmd>Trouble todo toggle filter = {tag = {TODO,FIX,FIXME}}
nnoremap  , <Cmd>FzfLua buffers sort_mru=true sort_lastused=true
nnoremap  sD <Cmd>FzfLua diagnostics_workspace
nnoremap  sd <Cmd>FzfLua diagnostics_document
nnoremap  sC <Cmd>FzfLua commands
nnoremap  sc <Cmd>FzfLua command_history
nnoremap  sb <Cmd>FzfLua grep_curbuf
nnoremap  sa <Cmd>FzfLua autocmds
nnoremap  s" <Cmd>FzfLua registers
nnoremap  gs <Cmd>FzfLua git_status
nnoremap  gc <Cmd>FzfLua git_commits
nnoremap  fr <Cmd>FzfLua oldfiles
nnoremap  fg <Cmd>FzfLua git_files
nnoremap  sR <Cmd>FzfLua resume
nnoremap  sm <Cmd>FzfLua marks
nnoremap  sM <Cmd>FzfLua man_pages
nnoremap  sl <Cmd>FzfLua loclist
nnoremap  sk <Cmd>FzfLua keymaps
nnoremap  sj <Cmd>FzfLua jumps
nnoremap  sH <Cmd>FzfLua highlights
nnoremap  sh <Cmd>FzfLua help_tags
nnoremap  fb <Cmd>FzfLua buffers sort_mru=true sort_lastused=true
nnoremap  : <Cmd>FzfLua command_history
nnoremap  sq <Cmd>FzfLua quickfix
nnoremap <silent>  	[ <Cmd>tabprevious
nnoremap <silent>  	d <Cmd>tabclose
nnoremap <silent>  	] <Cmd>tabnext
nnoremap <silent>  		 <Cmd>tabnew
nnoremap <silent>  	f <Cmd>tabfirst
nnoremap <silent>  	o <Cmd>tabonly
nnoremap <silent>  	l <Cmd>tablast
nnoremap <silent>  wd c
nnoremap <silent>  | v
nnoremap <silent>  - s
nnoremap <silent>  uI <Cmd>InspectTree
nnoremap <silent>  qq <Cmd>qa
nnoremap <silent>  fn <Cmd>enew
nnoremap <silent>  l <Cmd>Lazy
nnoremap <silent>  K <Cmd>norm! K
nnoremap <silent>  ur <Cmd>nohlsearch|diffupdate|normal! 
nnoremap <silent>  bD <Cmd>:bd
nnoremap <silent>  ` <Cmd>e #
nnoremap <silent>  bb <Cmd>e #
nnoremap  sn <Nop>
nnoremap  xX <Cmd>Trouble diagnostics toggle filter.buf=0
nnoremap  xx <Cmd>Trouble diagnostics toggle
nnoremap  xQ <Cmd>Trouble qflist toggle
nnoremap  xL <Cmd>Trouble loclist toggle
nnoremap  cS <Cmd>Trouble lsp toggle
nnoremap  cs <Cmd>Trouble symbols toggle
nnoremap  bl <Cmd>BufferLineCloseLeft
nnoremap  br <Cmd>BufferLineCloseRight
nnoremap  bP <Cmd>BufferLineGroupClose ungrouped
nnoremap  bp <Cmd>BufferLineTogglePin
nnoremap  cm <Cmd>Mason
nmap  E  fE
nmap  e  fe
omap <silent> % <Plug>(MatchitOperationForward)
xmap <silent> % <Plug>(MatchitVisualForward)
nmap <silent> % <Plug>(MatchitNormalForward)
nnoremap & :&&
nnoremap <p <Plug>(YankyPutIndentAfterShiftLeft)
nnoremap <P <Plug>(YankyPutIndentBeforeShiftLeft)
vnoremap <silent> < <gv
nnoremap =p <Plug>(YankyPutAfterFilter)
nnoremap =P <Plug>(YankyPutBeforeFilter)
nnoremap >p <Plug>(YankyPutIndentAfterShiftRight)
nnoremap >P <Plug>(YankyPutIndentBeforeShiftRight)
vnoremap <silent> > >gv
xnoremap <silent> <expr> @ mode() ==# 'V' ? ':normal! @'.getcharstr().'' : '@'
nnoremap H <Cmd>BufferLineCyclePrev
nnoremap L <Cmd>BufferLineCycleNext
onoremap <silent> <expr> N 'nN'[v:searchforward]
xnoremap <silent> <expr> N 'nN'[v:searchforward]
nnoremap <silent> <expr> N 'nN'[v:searchforward].'zv'
nnoremap P <Plug>(YankyPutBefore)
xnoremap P <Plug>(YankyPutBefore)
xnoremap <silent> <expr> Q mode() ==# 'V' ? ':normal! @=reg_recorded()' : 'Q'
nnoremap Y y$
nnoremap [P <Plug>(YankyPutIndentBeforeLinewise)
nnoremap [y <Plug>(YankyCycleForward)
nnoremap [p <Plug>(YankyPutIndentBeforeLinewise)
nnoremap [b <Cmd>BufferLineCyclePrev
nnoremap [B <Cmd>BufferLineMovePrev
omap <silent> [% <Plug>(MatchitOperationMultiBackward)
xmap <silent> [% <Plug>(MatchitVisualMultiBackward)
nmap <silent> [% <Plug>(MatchitNormalMultiBackward)
nnoremap ]y <Plug>(YankyCycleBackward)
nnoremap ]p <Plug>(YankyPutIndentAfterLinewise)
nnoremap ]P <Plug>(YankyPutIndentAfterLinewise)
nnoremap ]B <Cmd>BufferLineMoveNext
nnoremap ]b <Cmd>BufferLineCycleNext
omap <silent> ]% <Plug>(MatchitOperationMultiForward)
xmap <silent> ]% <Plug>(MatchitVisualMultiForward)
nmap <silent> ]% <Plug>(MatchitNormalMultiForward)
xmap a% <Plug>(MatchitVisualTextObject)
nnoremap gP <Plug>(YankyGPutBefore)
xnoremap gP <Plug>(YankyGPutBefore)
xnoremap gp <Plug>(YankyGPutAfter)
nnoremap gp <Plug>(YankyGPutAfter)
nnoremap <silent> gcO OVcx<Cmd>normal gccfxa<BS>
nnoremap <silent> gco oVcx<Cmd>normal gccfxa<BS>
omap <silent> g% <Plug>(MatchitOperationBackward)
xmap <silent> g% <Plug>(MatchitVisualBackward)
nmap <silent> g% <Plug>(MatchitNormalBackward)
xnoremap <silent> <expr> j v:count == 0 ? 'gj' : 'j'
nnoremap <silent> <expr> j v:count == 0 ? 'gj' : 'j'
xnoremap <silent> <expr> k v:count == 0 ? 'gk' : 'k'
nnoremap <silent> <expr> k v:count == 0 ? 'gk' : 'k'
onoremap <silent> <expr> n 'Nn'[v:searchforward]
xnoremap <silent> <expr> n 'Nn'[v:searchforward]
nnoremap <silent> <expr> n 'Nn'[v:searchforward].'zv'
nnoremap p <Plug>(YankyPutAfter)
xnoremap p <Plug>(YankyPutAfter)
nnoremap y <Plug>(YankyYank)
xnoremap y <Plug>(YankyYank)
snoremap <expr> <BS> ("\<BS>" . (&virtualedit ==# '' && getcurpos()[2] >= col('$') - 1 ? 'a' : 'i'))
tnoremap <silent> <C-_> <Cmd>close
tnoremap <silent> <C-/> <Cmd>close
snoremap <silent> <C-S> <Cmd>w
nnoremap <silent> <C-S> <Cmd>w
xnoremap <silent> <C-S> <Cmd>w
vnoremap <silent> <M-k> :execute "'<,'>move '<-" . (v:count1 + 1)gv=gv
vnoremap <silent> <M-j> :execute "'<,'>move '>+" . v:count1gv=gv
nnoremap <silent> <M-k> <Cmd>execute 'move .-' . (v:count1 + 1)==
nnoremap <silent> <M-j> <Cmd>execute 'move .+' . v:count1==
nnoremap <silent> <C-Right> <Cmd>vertical resize +2
nnoremap <silent> <C-Left> <Cmd>vertical resize -2
nnoremap <silent> <C-Down> <Cmd>resize -2
nnoremap <silent> <C-Up> <Cmd>resize +2
nnoremap <silent> <C-K> k
nnoremap <silent> <C-J> j
nnoremap <silent> <C-H> h
xnoremap <silent> <expr> <Up> v:count == 0 ? 'gk' : 'k'
nnoremap <silent> <expr> <Up> v:count == 0 ? 'gk' : 'k'
xnoremap <silent> <expr> <Down> v:count == 0 ? 'gj' : 'j'
nnoremap <silent> <expr> <Down> v:count == 0 ? 'gj' : 'j'
nnoremap <silent> <C-U> zz
nnoremap <silent> <C-D> zz
xmap <silent> <Plug>(MatchitVisualTextObject) <Plug>(MatchitVisualMultiBackward)o<Plug>(MatchitVisualMultiForward)
onoremap <silent> <Plug>(MatchitOperationMultiForward) :call matchit#MultiMatch("W",  "o")
onoremap <silent> <Plug>(MatchitOperationMultiBackward) :call matchit#MultiMatch("bW", "o")
xnoremap <silent> <Plug>(MatchitVisualMultiForward) :call matchit#MultiMatch("W",  "n")m'gv``
xnoremap <silent> <Plug>(MatchitVisualMultiBackward) :call matchit#MultiMatch("bW", "n")m'gv``
nnoremap <silent> <Plug>(MatchitNormalMultiForward) :call matchit#MultiMatch("W",  "n")
nnoremap <silent> <Plug>(MatchitNormalMultiBackward) :call matchit#MultiMatch("bW", "n")
onoremap <silent> <Plug>(MatchitOperationBackward) :call matchit#Match_wrapper('',0,'o')
onoremap <silent> <Plug>(MatchitOperationForward) :call matchit#Match_wrapper('',1,'o')
xnoremap <silent> <Plug>(MatchitVisualBackward) :call matchit#Match_wrapper('',0,'v')m'gv``
xnoremap <silent> <Plug>(MatchitVisualForward) :call matchit#Match_wrapper('',1,'v'):if col("''") != col("$") | exe ":normal! m'" | endifgv``
nnoremap <silent> <Plug>(MatchitNormalBackward) :call matchit#Match_wrapper('',0,'n')
nnoremap <silent> <Plug>(MatchitNormalForward) :call matchit#Match_wrapper('',1,'n')
nmap <C-W><C-D> d
nnoremap <silent> <C-L> l
inoremap <silent> <expr>  v:lua.MiniPairs.cr()
inoremap <silent>  <Cmd>w
inoremap  u
inoremap  u
inoremap <expr> " v:lua.MiniPairs.closeopen('""', "[^\\].")
cnoremap <expr> " v:lua.MiniPairs.closeopen('""', "[^\\].")
inoremap <expr> ' v:lua.MiniPairs.closeopen("''", "[^%a\\].")
cnoremap <expr> ' v:lua.MiniPairs.closeopen("''", "[^%a\\].")
inoremap <expr> ( v:lua.MiniPairs.open("()", "[^\\].")
cnoremap <expr> ( v:lua.MiniPairs.open("()", "[^\\].")
inoremap <expr> ) v:lua.MiniPairs.close("()", "[^\\].")
cnoremap <expr> ) v:lua.MiniPairs.close("()", "[^\\].")
inoremap <silent> , ,u
inoremap <silent> . .u
inoremap <silent> ; ;u
inoremap <expr> [ v:lua.MiniPairs.open("[]", "[^\\].")
cnoremap <expr> [ v:lua.MiniPairs.open("[]", "[^\\].")
inoremap <expr> ] v:lua.MiniPairs.close("[]", "[^\\].")
cnoremap <expr> ] v:lua.MiniPairs.close("[]", "[^\\].")
inoremap <expr> ` v:lua.MiniPairs.closeopen("``", "[^\\].")
cnoremap <expr> ` v:lua.MiniPairs.closeopen("``", "[^\\].")
inoremap <expr> { v:lua.MiniPairs.open("{}", "[^\\].")
cnoremap <expr> { v:lua.MiniPairs.open("{}", "[^\\].")
inoremap <expr> } v:lua.MiniPairs.close("{}", "[^\\].")
cnoremap <expr> } v:lua.MiniPairs.close("{}", "[^\\].")
let &cpo=s:cpo_save
unlet s:cpo_save
set autowrite
set background=light
set clipboard=unnamedplus
set cmdheight=0
set completeopt=menu,menuone,noselect
set confirm
set expandtab
set fillchars=diff:╱,eob:\ ,fold:\ ,foldclose:,foldopen:,foldsep:\ 
set formatexpr=v:lua.require'lazyvim.util'.format.formatexpr()
set formatoptions=jcroqlnt
set grepformat=%f:%l:%c:%m
set grepprg=rg\ --vimgrep
set helplang=en
set ignorecase
set jumpoptions=view
set laststatus=3
set noloadplugins
set mouse=a
set packpath=/home/linuxbrew/.linuxbrew/Cellar/neovim/0.10.3/share/nvim/runtime
set pumblend=10
set pumheight=10
set noruler
set runtimepath=~/.config/nvim,~/.local/share/nvim/site,~/.local/share/nvim/lazy/lazy.nvim,~/.local/share/nvim/lazy/vim-vsnip,~/.local/share/nvim/lazy/clangd_extensions.nvim,~/.local/share/nvim/lazy/friendly-snippets,~/.local/share/nvim/lazy/blink.cmp,~/.local/share/nvim/lazy/mason-lspconfig.nvim,~/.local/share/nvim/lazy/nvim-lspconfig,~/.local/share/nvim/lazy/yanky.nvim,~/.local/share/nvim/lazy/todo-comments.nvim,~/.local/share/nvim/lazy/nvim-lint,~/.local/share/nvim/lazy/mini.hipatterns,~/.local/share/nvim/lazy/gitsigns.nvim,~/.local/share/nvim/lazy/nvim-ts-autotag,~/.local/share/nvim/lazy/persistence.nvim,~/.local/share/nvim/lazy/mini.icons,~/.local/share/nvim/lazy/fzf-lua,~/.local/share/nvim/lazy/nui.nvim,~/.local/share/nvim/lazy/nvim-treesitter-textobjects,~/.local/share/nvim/lazy/ts-comments.nvim,~/.local/share/nvim/lazy/which-key.nvim,~/.local/share/nvim/lazy/mini.pairs,~/.local/share/nvim/lazy/mini.ai,~/.local/share/nvim/lazy/noice.nvim,~/.local/share/nvim/lazy/flash.nvim,~/.local/share/nvim/lazy/trouble.nvim,~/.local/share/nvim/lazy/lualine.nvim,~/.local/share/nvim/lazy/bufferline.nvim,~/.local/share/nvim/lazy/nvim-treesitter,~/.local/share/nvim/lazy/mason.nvim,~/.local/share/nvim/lazy/conform.nvim,~/.local/share/nvim/lazy/neo-tree.nvim,~/.local/share/nvim/lazy/catppuccin,~/.local/share/nvim/lazy/melange-nvim,~/.local/share/nvim/lazy/transparent.nvim,~/.local/share/nvim/lazy/onedarkpro.nvim,~/.local/share/nvim/lazy/snacks.nvim,~/.local/share/nvim/lazy/tokyonight.nvim,~/.local/share/nvim/lazy/LazyVim,/home/linuxbrew/.linuxbrew/Cellar/neovim/0.10.3/share/nvim/runtime,/home/linuxbrew/.linuxbrew/Cellar/neovim/0.10.3/share/nvim/runtime/pack/dist/opt/matchit,/home/linuxbrew/.linuxbrew/Cellar/neovim/0.10.3/lib/nvim,~/.local/state/nvim/lazy/readme,~/.local/share/nvim/lazy/catppuccin/after,~/.local/share/nvim/lazy/onedarkpro.nvim/after
set scrolloff=4
set sessionoptions=buffers,curdir,tabpages,winsize,help,globals,skiprtp,folds
set shiftround
set shiftwidth=2
set shortmess=OlCTtFcoWI
set noshowmode
set showtabline=0
set sidescrolloff=8
set smartcase
set smartindent
set spelllang=fr,en
set splitbelow
set splitkeep=screen
set splitright
set statusline=%#Normal#
set tabline=%!v:lua.nvim_bufferline()
set tabstop=2
set termguicolors
set timeoutlen=300
set undofile
set undolevels=10000
set updatetime=200
set virtualedit=block
set wildmode=longest:full,full
set window=57
set winminwidth=5
" vim: set ft=vim :
