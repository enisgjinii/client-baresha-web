<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>

<div class="col-md-10 main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>Paneli i Kontrollit</h4>
        <div>
            <button class="btn btn-light rounded-circle">
                <i class="bi bi-search"></i>
            </button>
            <button class="btn btn-light rounded-circle ms-2">
                <i class="bi bi-gear"></i>
            </button>
        </div>
    </div>
    <div class="row">
        <div class="col-md-3">
            <div class="card p-3">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="text-muted mb-1">Shitje të reja</p>
                        <h4 class="mb-0">1,345 <small class="text-success"><i class="bi bi-arrow-up-short"></i></small></h4>
                    </div>
                    <div class="metric-icon bg-light-blue text-primary">
                        <i class="bi bi-graph-up"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="text-muted mb-1">Klientë të rinj potencialë</p>
                        <h4 class="mb-0">2,890 <small class="text-success"><i class="bi bi-arrow-up-short"></i></small></h4>
                    </div>
                    <div class="metric-icon bg-light-orange text-warning">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="text-muted mb-1">Të ardhura për klient potencial</p>
                        <h4 class="mb-0">$1,870</h4>
                    </div>
                    <div class="metric-icon bg-light-blue text-primary">
                        <i class="bi bi-wallet2"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="text-muted mb-1">Norma e konvertimit</p>
                        <h4 class="mb-0">5.10% <small class="text-success"><i class="bi bi-arrow-up-short"></i></small></h4>
                    </div>
                    <div class="metric-icon bg-light-red text-danger">
                        <i class="bi bi-arrow-left-right"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card mt-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title">Shitjet e fundit</h5>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-sm btn-outline-secondary">Dita</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary">Java</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary">Muaji</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-grid"></i>
                    </button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Produkti</th>
                            <th>Klienti</th>
                            <th>Dërgesa</th>
                            <th>Nëntotali</th>
                            <th>Transporti</th>
                            <th>Totali</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="product-img me-2 d-flex align-items-center justify-content-center">
                                        <i class="bi bi-laptop"></i>
                                    </div>
                                    <div>
                                        <div>Macbook Pro</div>
                                        <small class="text-muted">ID 10-3290-08</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>Rodney Cannon</div>
                                <small class="text-muted">rodney.cannon@gmail.com</small>
                            </td>
                            <td>
                                <div>Mbretëria e Bashkuar</div>
                                <small class="text-muted">193 Cole Plains Suite 649, 891203</small>
                            </td>
                            <td>$100.00</td>
                            <td>$18.00</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="badge badge-shipped px-2 py-1 me-2">Dërguar</span>
                                    $118.00
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="product-img me-2 d-flex align-items-center justify-content-center">
                                        <i class="bi bi-laptop"></i>
                                    </div>
                                    <div>
                                        <div>Dell Laptop</div>
                                        <small class="text-muted">ID 10-3456-18</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>Mike Franklin</div>
                                <small class="text-muted">mike.franklin@gmail.com</small>
                            </td>
                            <td>
                                <div>Shtetet e Bashkuara</div>
                                <small class="text-muted">619 Jeffrey Freeway Apt. 273</small>
                            </td>
                            <td>$180.00</td>
                            <td>$28.00</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="badge badge-processing px-2 py-1 me-2">Duke u përpunuar</span>
                                    $208.00
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="product-img me-2 d-flex align-items-center justify-content-center">
                                        <i class="bi bi-laptop"></i>
                                    </div>
                                    <div>
                                        <div>Macbook Air</div>
                                        <small class="text-muted">ID 10-3786-23</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>Louis Franklin</div>
                                <small class="text-muted">louis.franklin@gmail.com</small>
                            </td>
                            <td>
                                <div>Gjermania</div>
                                <small class="text-muted">200 Davis Estates Suite 621</small>
                            </td>
                            <td>$100.00</td>
                            <td>$18.00</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="badge badge-processing px-2 py-1 me-2">Duke u përpunuar</span>
                                    $118.00
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="product-img me-2 d-flex align-items-center justify-content-center">
                                        <i class="bi bi-laptop"></i>
                                    </div>
                                    <div>
                                        <div>Macbook</div>
                                        <small class="text-muted">ID 10-4570-15</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>Dominic Love</div>
                                <small class="text-muted">dominic.love@gmail.com</small>
                            </td>
                            <td>
                                <div>Spanja</div>
                                <small class="text-muted">742 Rau Summit Suite 407</small>
                            </td>
                            <td>$560.00</td>
                            <td>$18.00</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="badge badge-shipped px-2 py-1 me-2">Dërguar</span>
                                    $578.00
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="product-img me-2 d-flex align-items-center justify-content-center">
                                        <i class="bi bi-laptop"></i>
                                    </div>
                                    <div>
                                        <div>LG Laptop</div>
                                        <small class="text-muted">ID 10-5468-19</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>Adrian Drake</div>
                                <small class="text-muted">adrian.drake@gmail.com</small>
                            </td>
                            <td>
                                <div>Mbretëria e Bashkuar</div>
                                <small class="text-muted">166 Corkery Vista Apt. 293</small>
                            </td>
                            <td>$340.00</td>
                            <td>$34.00</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="badge badge-cancelled px-2 py-1 me-2">Anuluar</span>
                                    $374.00
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="product-img me-2 d-flex align-items-center justify-content-center">
                                        <i class="bi bi-laptop"></i>
                                    </div>
                                    <div>
                                        <div>Macbook Pro</div>
                                        <small class="text-muted">ID 10-4890-14</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>Walter Quinn</div>
                                <small class="text-muted">walter.quinn@gmail.com</small>
                            </td>
                            <td>
                                <div>Shtetet e Bashkuara</div>
                                <small class="text-muted">7613 Wilfredo Rapids Apt. 715</small>
                            </td>
                            <td>$180.00</td>
                            <td>$40.00</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="badge badge-shipped px-2 py-1 me-2">Dërguar</span>
                                    $220.00
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="product-img me-2 d-flex align-items-center justify-content-center">
                                        <i class="bi bi-laptop"></i>
                                    </div>
                                    <div>
                                        <div>Macbook Pro</div>
                                        <small class="text-muted">ID 10-3290-08</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>Victor Roberson</div>
                                <small class="text-muted">victor.roberson@gmail.com</small>
                            </td>
                            <td>
                                <div>Gjermania</div>
                                <small class="text-muted">357 Hoeger Bypass Apt. 593</small>
                            </td>
                            <td>$680.00</td>
                            <td>$18.00</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="badge badge-shipped px-2 py-1 me-2">Dërguar</span>
                                    $698.00
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <nav>
                <ul class="pagination justify-content-center">
                    <li class="page-item">
                        <a class="page-link" href="#" aria-label="Previous">
                            <span aria-hidden="true">&laquo; PREV</span>
                        </a>
                    </li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item"><a class="page-link" href="#">4</a></li>
                    <li class="page-item"><a class="page-link" href="#">5</a></li>
                    <li class="page-item">
                        <a class="page-link" href="#" aria-label="Next">
                            <span aria-hidden="true">NEXT &raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
<style>
    .main-content {
        padding: 2rem;
    }

    .metric-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .bg-light-blue {
        background-color: #e0f7fa !important;
    }

    .bg-light-orange {
        background-color: #fff3e0 !important;
    }

    .bg-light-red {
        background-color: #ffebee !important;
    }

    .badge-shipped {
        background-color: #c8e6c9;
        color: #2e7d32;
    }

    .badge-processing {
        background-color: #ffecb3;
        color: #ff6f00;
    }

    .badge-cancelled {
        background-color: #ffcdd2;
        color: #b71c1c;
    }

    .product-img {
        width: 40px;
        height: 40px;
        border-radius: 5px;
        background-color: #f8f9fa;
        color: #495057;
    }
</style>
